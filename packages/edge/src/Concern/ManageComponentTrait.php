<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Concern;

use Windwalker\Edge\Wrapper\SlotWrapper;
use Windwalker\Utilities\Symbol;

use function Windwalker\nope;

/**
 * The ComponentConcernTrait class.
 *
 * @since  3.3.1
 */
trait ManageComponentTrait
{
    /**
     * The components being rendered.
     *
     * @var array
     */
    protected array $componentStack = [];

    /**
     * The original data passed to the component.
     *
     * @var array
     */
    protected array $componentData = [];

    /**
     * The slot contents for the component.
     *
     * @var array
     */
    protected array $slots = [];

    /**
     * The names of the slots being rendered.
     *
     * @var array
     */
    protected array $slotStack = [];

    /**
     * Start a component rendering process.
     *
     * @param  string  $name
     * @param  array   $data
     *
     * @return void
     */
    public function startComponent(string $name, array $data = [])
    {
        if (ob_start()) {
            $this->componentStack[]                         = $name;
            $this->componentData[$this->currentComponent()] = $data;
            $this->slots[$this->currentComponent()]         = [];
        }
    }

    /**
     * Render the current component.
     *
     * @return string
     */
    public function renderComponent(): string
    {
        $slot = $this->slots[$this->currentComponent()][Symbol::root()->getValue()] ?? null;

        $name = array_pop($this->componentStack);

        return $this->render($name, $this->componentData($slot));
    }

    /**
     * Get the data for the given component.
     *
     * @param  callable|null  $slot
     *
     * @return array
     */
    protected function componentData(?callable $slot): array
    {
        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => $slot],
            $this->slots[count($this->componentStack)]
        );
    }

    /**
     * Start the slot rendering process.
     *
     * @param  string|null  $name
     * @param  string|null  $content
     *
     * @return \Closure
     */
    public function slot(?string $name = null, ?string $content = null): \Closure
    {
        $name ??= Symbol::root()->getValue();

        if ($content !== null) {
            $this->slots[$this->currentComponent()][$name] = new SlotWrapper(
                function () use ($content) {
                    echo $content;
                }
            );
        }

        return function ($renderer) use ($name) {
            $this->slots[$this->currentComponent()][$name] = new SlotWrapper($renderer);
            $this->slotStack[$this->currentComponent()][]  = $name;
        };
    }

    /**
     * Save the slot content for rendering.
     *
     * @return void
     */
    public function endSlot(): void
    {
        end($this->componentStack);

        $currentSlot = array_pop(
            $this->slotStack[$this->currentComponent()]
        );
    }

    /**
     * Get the index for the current component.
     *
     * @return int
     */
    protected function currentComponent(): int
    {
        return count($this->componentStack) - 1;
    }
}
