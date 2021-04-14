<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge;

use Closure;
use Throwable;
use Windwalker\Edge\Cache\EdgeArrayCache;
use Windwalker\Edge\Cache\EdgeCacheInterface;
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Compiler\EdgeCompiler;
use Windwalker\Edge\Compiler\EdgeCompilerInterface;
use Windwalker\Edge\Concern\ManageComponentTrait;
use Windwalker\Edge\Concern\ManageEventTrait;
use Windwalker\Edge\Concern\ManageLayoutTrait;
use Windwalker\Edge\Concern\ManageStackTrait;
use Windwalker\Edge\Exception\EdgeException;
use Windwalker\Edge\Extension\DirectivesExtensionInterface;
use Windwalker\Edge\Extension\EdgeExtensionInterface;
use Windwalker\Edge\Extension\GlobalVariablesExtensionInterface;
use Windwalker\Edge\Extension\ParsersExtensionInterface;
use Windwalker\Edge\Loader\EdgeLoaderInterface;
use Windwalker\Edge\Loader\EdgeStringLoader;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Classes\ObjectBuilderAwareTrait;
use Windwalker\Utilities\Wrapper\RawWrapper;

/**
 * The Edge template engine.
 *
 * This is a modified version of Laravel Blade engine.
 *
 * @see    https://github.com/illuminate/view/blob/master/Factory.php
 *
 * @since  3.0
 */
class Edge
{
    use ManageComponentTrait;
    use ManageEventTrait;
    use ManageLayoutTrait;
    use ManageStackTrait;
    use ObjectBuilderAwareTrait;

    /**
     * Property globals.
     *
     * @var  array
     */
    protected array $globals = [];

    /**
     * Property extensions.
     *
     * @var  EdgeExtensionInterface[]
     */
    protected array $extensions = [];

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected int $renderCount = 0;

    /**
     * Property loader.
     *
     * @var EdgeLoaderInterface|null
     */
    protected ?EdgeLoaderInterface $loader = null;

    /**
     * Property compiler.
     *
     * @var  ?EdgeCompilerInterface
     */
    protected ?EdgeCompilerInterface $compiler = null;

    /**
     * Property cacheHandler.
     *
     * @var EdgeCacheInterface|null
     */
    protected ?EdgeCacheInterface $cache = null;

    protected ?object $context = null;

    /**
     * EdgeEnvironment constructor.
     *
     * @param  EdgeLoaderInterface|null    $loader
     * @param  EdgeCacheInterface|null     $cache
     * @param  EdgeCompilerInterface|null  $compiler
     */
    public function __construct(
        EdgeLoaderInterface $loader = null,
        EdgeCacheInterface $cache = null,
        EdgeCompilerInterface $compiler = null
    ) {
        $this->loader   = $loader ?: new EdgeStringLoader();
        $this->compiler = $compiler ?: new EdgeCompiler();
        $this->cache    = $cache ?: new EdgeArrayCache();
    }

    /**
     * renderWithContext
     *
     * @param  string       $layout
     * @param  array        $data
     * @param  object|null  $context
     *
     * @return  string
     *
     * @throws EdgeException
     */
    public function renderWithContext(string $layout, array $data = [], ?object $context = null): string
    {
        $this->context = $context;

        $result = $this->render($layout, $data);

        $this->context = null;

        return $result;
    }

    /**
     * render
     *
     * @param  string|callable  $__layout
     * @param  array            $__data
     * @param  array            $__more
     *
     * @return string
     * @throws EdgeException
     */
    public function render(string|Closure $__layout, array $__data = [], array $__more = []): string
    {
        // TODO: Aliases

        $this->incrementRender();

        if (!$__layout instanceof Closure) {
            $__path = $this->loader->find($__layout);

            if ($this->cache->isExpired($__path)) {
                $compiled = $this->compile($this->loader->load($__path));

                $this->cache->store($__path, $compiled);

                unset($compiler, $compiled);
            }
        } else {
            $__path = $__layout;
        }

        $__data = array_merge($this->getGlobals(true), $__more, $__data);

        unset($__data['__path'], $__data['__data']);

        $closure = $this->getRenderFunction($__data);

        if ($this->getContext()) {
            $closure = $closure->bindTo($this->getContext(), $this->getContext());
        }

        ob_start();

        try {
            $closure($__path);
        } catch (Throwable $e) {
            ob_clean();
            $this->wrapException($e, $__path, $__layout);

            return '';
        }

        $result = ltrim(ob_get_clean());

        $this->decrementRender();

        $this->flushSectionsIfDoneRendering();

        return $result;
    }

    public function compile(string|Closure $path): string
    {
        $compiler = $this->prepareExtensions(clone $this->compiler);

        if ($path instanceof Closure) {
            $path = $path($this);
        }

        return $compiler->compile($path);
    }

    protected function getRenderFunction(array $data): Closure
    {
        $__data = $data;
        $__edge = $this;

        return function ($__path) use ($__data, $__edge) {
            extract($__data, EXTR_OVERWRITE);

            if ($__path instanceof Closure) {
                eval(' ?>' . $this->compile($__path($this)) . '<?php ');
                return;
            }

            if ($__edge->getCache() instanceof EdgeFileCache) {
                include $__edge->getCache()->getCacheFile($__edge->getCache()->getCacheKey($__path));
            } else {
                eval(' ?>' . $__edge->getCache()->load($__path) . '<?php ');
            }
        };
    }

    /**
     * wrapException
     *
     * @param  Throwable  $e
     * @param  string     $path
     * @param  string     $layout
     *
     * @return  void
     *
     * @throws EdgeException
     */
    protected function wrapException(Throwable $e, string $path, string $layout): void
    {
        $msg = $e->getMessage();

        $msg .= sprintf("\n\n| View layout: %s (%s)", $path, $layout);

        $cache = $this->getCache();

        if ($cache instanceof EdgeFileCache) {
            if (str_starts_with(realpath($cache->getPath()), $e->getFile())) {
                throw new EdgeException($msg, $e->getCode(), $path, $e->getLine());
            }
        }

        throw new EdgeException($msg, $e->getCode(), null, null, $e);
    }

    /**
     * @return object|null
     */
    public function getContext(): ?object
    {
        return $this->context;
    }

    /**
     * @param  object|null  $context
     *
     * @return  static  Return self to support chaining.
     */
    public function setContext(?object $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Normalize a view name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        // TODO: Handle namespace

        return str_replace('/', '.', $name);
    }

    /**
     * escape
     *
     * @param  mixed  $string
     *
     * @return  string
     */
    public function escape(mixed $string): string
    {
        if ($string instanceof RawWrapper) {
            return $string->get();
        }

        return htmlspecialchars((string) $string, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @param  string  $layout
     * @param  array   $data
     * @param  string  $iterator
     * @param  string  $empty
     *
     * @return string
     * @throws EdgeException
     */
    public function renderEach(string $layout, array $data, string $iterator, string $empty = 'raw|'): string
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $data = ['key' => $key, $iterator => $value];

                $result .= $this->render($layout, $data);
            }
        } elseif (str_starts_with($empty, 'raw|')) {
            // If there is no data in the array, we will render the contents of the empty
            // view. Alternatively, the "empty view" could be a raw string that begins
            // with "raw|" for convenience and to let this know that it is a string.
            $result = substr($empty, 4);
        } else {
            $result = $this->render($empty);
        }

        return $result;
    }

    /**
     * Increment the rendering counter.
     *
     * @return void
     */
    public function incrementRender(): void
    {
        $this->renderCount++;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender(): void
    {
        $this->renderCount--;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering(): bool
    {
        return $this->renderCount === 0;
    }

    /**
     * prepareDirectives
     *
     * @param  EdgeCompilerInterface  $compiler
     *
     * @return EdgeCompilerInterface
     */
    public function prepareExtensions(EdgeCompilerInterface $compiler): EdgeCompilerInterface
    {
        foreach ($this->getExtensions() as $extension) {
            if ($extension instanceof DirectivesExtensionInterface) {
                foreach ($extension->getDirectives() as $name => $directive) {
                    $compiler->directive($name, $directive);
                }
            }

            if ($extension instanceof ParsersExtensionInterface) {
                foreach ($extension->getParsers() as $parser) {
                    $compiler->parser($parser);
                }
            }
        }

        return $compiler;
    }

    /**
     * arrayExcept
     *
     * @param  array  $array
     * @param  array  $fields
     *
     * @return  array
     */
    public function except(array $array, array $fields): array
    {
        return Arr::except($array, $fields);
    }

    /**
     * Method to get property Globals
     *
     * @param  bool  $withExtensions
     *
     * @return array
     */
    public function getGlobals($withExtensions = false): array
    {
        $globals = $this->globals;

        if ($withExtensions) {
            $values = [];

            foreach ($this->getExtensions() as $extension) {
                if ($extension instanceof GlobalVariablesExtensionInterface) {
                    $values[] = $extension->getGlobals();
                }
            }

            $values[] = $globals;

            $globals = array_merge(...$values);
        }

        return $globals;
    }

    /**
     * addGlobal
     *
     * @param  string  $name
     * @param  string  $value
     *
     * @return  static
     */
    public function addGlobal(string $name, string $value): static
    {
        $this->globals[$name] = $value;

        return $this;
    }

    /**
     * removeGlobal
     *
     * @param  string  $name
     *
     * @return  static
     */
    public function removeGlobal(string $name): static
    {
        unset($this->globals[$name]);

        return $this;
    }

    public function getGlobal(string $name, $default = null)
    {
        if (array_key_exists($name, $this->globals)) {
            return $this->globals[$name];
        }

        return $default;
    }

    /**
     * Method to set property globals
     *
     * @param  array  $globals
     *
     * @return  static  Return self to support chaining.
     */
    public function setGlobals(array $globals): array
    {
        $this->globals = $globals;

        return $this;
    }

    /**
     * Method to get property Compiler
     *
     * @return  EdgeCompilerInterface
     */
    public function getCompiler(): EdgeCompilerInterface
    {
        return $this->compiler;
    }

    /**
     * Method to set property compiler
     *
     * @param  EdgeCompilerInterface  $compiler
     *
     * @return  static  Return self to support chaining.
     */
    public function setCompiler(EdgeCompilerInterface $compiler): static
    {
        $this->compiler = $compiler;

        return $this;
    }

    /**
     * Method to get property Loader
     *
     * @return  EdgeLoaderInterface
     */
    public function getLoader(): EdgeLoaderInterface
    {
        return $this->loader;
    }

    /**
     * Method to set property loader
     *
     * @param  EdgeLoaderInterface  $loader
     *
     * @return  static  Return self to support chaining.
     */
    public function setLoader(EdgeLoaderInterface $loader): static
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * addExtension
     *
     * @param  EdgeExtensionInterface  $extension
     * @param  string|null             $name
     *
     * @return static
     */
    public function addExtension(EdgeExtensionInterface $extension, ?string $name = null): static
    {
        if (!$name) {
            $name = $extension->getName();
        }

        $this->extensions[$name] = $extension;

        return $this;
    }

    /**
     * removeExtension
     *
     * @param  string  $name
     *
     * @return  static
     */
    public function removeExtension(string $name): static
    {
        if (array_key_exists($name, $this->extensions)) {
            unset($this->extensions[$name]);
        }

        return $this;
    }

    /**
     * hasExtension
     *
     * @param  string  $name
     *
     * @return  bool
     */
    public function hasExtension(string $name): bool
    {
        return array_key_exists($name, $this->extensions) && $this->extensions[$name] instanceof EdgeExtensionInterface;
    }

    /**
     * getExtension
     *
     * @param  string  $name
     *
     * @return  EdgeExtensionInterface
     */
    public function getExtension(string $name): ?EdgeExtensionInterface
    {
        if ($this->hasExtension($name)) {
            return $this->extensions[$name];
        }

        return null;
    }

    /**
     * Method to get property Extensions
     *
     * @return  Extension\EdgeExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Method to set property extensions
     *
     * @param  Extension\EdgeExtensionInterface[]  $extensions
     *
     * @return  static  Return self to support chaining.
     */
    public function setExtensions(array $extensions): static
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * Method to get property Cache
     *
     * @return  EdgeCacheInterface
     */
    public function getCache(): EdgeCacheInterface
    {
        return $this->cache;
    }

    /**
     * Method to set property cache
     *
     * @param  EdgeCacheInterface  $cache
     *
     * @return  static  Return self to support chaining.
     */
    public function setCache(EdgeCacheInterface $cache): static
    {
        $this->cache = $cache;

        return $this;
    }

    public function make(string $class, ...$args): object
    {
        return $this->getObjectBuilder()->createObject($class, ...$args);
    }
}
