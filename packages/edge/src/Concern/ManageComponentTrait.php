<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Edge\Concern;

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
     * @param  string $name
     * @param  array  $data
     * @param  array  $more
     *
     * @return void
     */
    public function startComponent(string $name, array $data = [], array $more = [])
    {
        if (ob_start()) {
            $this->componentStack[] = $name;
            $this->componentData[$this->currentComponent()] = array_merge($more, $data);
            $this->slots[$this->currentComponent()] = [];
        }
    }

    /**
     * Render the current component.
     *
     * @return string
     */
    public function renderComponent(): string
    {
        $name = array_pop($this->componentStack);

        return $this->render($name, $this->componentData($name));
    }

    /**
     * Get the data for the given component.
     *
     * @param  string  $name
     *
     * @return array
     */
    protected function componentData(string $name): array
    {
        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => trim(ob_get_clean())],
            $this->slots[count($this->componentStack)]
        );
    }

    /**
     * Start the slot rendering process.
     *
     * @param  string       $name
     * @param  string|null  $content
     *
     * @return void
     */
    public function slot(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            $this->slots[$this->currentComponent()][$name] = $content;
        } elseif (ob_start()) {
            $this->slots[$this->currentComponent()][$name] = '';
            $this->slotStack[$this->currentComponent()][] = $name;
        }
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
        $this->slots[$this->currentComponent()][$currentSlot] = trim(ob_get_clean());
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
