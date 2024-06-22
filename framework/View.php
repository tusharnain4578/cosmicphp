<?php

namespace Framework;

class View
{
    private const TEMPLATE_DIRECTORY = 'Templates';
    private const TEMPLATE_EXTENSION = '.phtml';
    private string $layoutFile;
    private array $sections = [];
    private array $sectionStack;
    private string $currentSection;

    public function layout(string $layoutView): self
    {
        $this->layoutFile = $this->getFullViewPath($layoutView);
        return $this;
    }

    public function section(string $name)
    {
        $this->currentSection = $name;
        $this->sectionStack[] = $name;

        ob_start();
    }

    public function endSection()
    {
        $contents = ob_get_clean();

        if ($this->sectionStack === []) {
            throw new \Exception('View themes, no current section.');
        }

        $section = array_pop($this->sectionStack);

        if (!array_key_exists($section, $this->sections)) {
            $this->sections[$section] = [];
        }

        $this->sections[$section][] = $contents;

    }
    public function renderSection(string $sectionName, bool $saveData = false)
    {
        if (!isset($this->sections[$sectionName])) {
            echo '';

            return;
        }

        foreach ($this->sections[$sectionName] as $key => $contents) {
            echo $contents;
        }
    }

    public function render(string $view, array $data = []): string
    {
        foreach ($data as $varName => $varValue)
            ${$varName} = $varValue;

        ob_start();
        require_once $this->getFullViewPath($view);
        $viewContent = ob_get_clean();

        if (isset($this->layoutFile) and $this->layoutFile) {
            ob_start();
            require_once $this->layoutFile;
            $viewContent = ob_get_clean();
        }

        return $viewContent;
    }


    private function getFullViewPath(string $viewFileName): string
    {
        $viewFileName = trim($viewFileName, '\.\\\/\ ');
        $viewFileName = str_replace('/', DIRECTORY_SEPARATOR, $viewFileName);

        // remove below line to disable view name . seperation
        $viewFileName = str_replace('.', DIRECTORY_SEPARATOR, $viewFileName);

        $filePathArray = [Path::appPath(), self::TEMPLATE_DIRECTORY, $viewFileName];
        $fullFilePath = implode(DIRECTORY_SEPARATOR, $filePathArray);

        if (!str_ends_with($viewFileName, self::TEMPLATE_EXTENSION))
            $fullFilePath .= self::TEMPLATE_EXTENSION;

        return $fullFilePath;
    }
}