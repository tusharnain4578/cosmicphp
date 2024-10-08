<?php

namespace Core;

use Core\Utilities\Path;
use Core\Utilities\File;

class View
{
    public const TEMPLATE_DIRECTORY = 'Views';
    public const FRAMEWORK_TEMPLATE_DIRECTORY = 'Views';
    public const TEMPLATE_EXTENSION = '.phtml';
    private string $layoutFile;
    private array $sections = [];
    private array $sectionStack = [];
    private string $currentSection;
    private static $error = null;
    private bool $isCore = false;

    public function core(): self
    {
        $this->isCore = true;
        return $this;
    }

    public function layout(string $layoutView): self
    {
        if (isset($this->layoutFile))
            throw new \Exception("Layout can only be set once per view.");

        $layoutViewPath = $this->getFullViewPath($layoutView);

        if (!File::exists($layoutViewPath))
            throw new \Exception("Layout Template File : $layoutViewPath, doesn't exists.");
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

        foreach ($this->sections[$sectionName] as $key => $contents)
            echo $contents;
    }

    public function render(string $view, array $data = []): string
    {
        $viewFilePath = $this->getFullViewPath($view);

        if (!File::exists($viewFilePath))
            throw new \Exception("Template File :  $viewFilePath, doesn't exists.");


        $viewContent = (function () use ($viewFilePath, $data): string{
            extract($data);
            ob_start();
            require_once $viewFilePath;
            return ob_get_clean();
        })();

        if (isset($this->layoutFile) and $this->layoutFile) {
            $viewContent = (function (): string{
                ob_start();
                require_once $this->layoutFile;
                return ob_get_clean();
            })();
        }

        if (self::$error) {
            if (ob_get_level() > 0)
                ob_end_clean();
            throw new \Exception(self::$error);
        }

        return $viewContent;
    }


    private function getFullViewPath(string $viewFileName): string
    {
        $viewFileName = trim($viewFileName, '\.\\\/\ ');
        $viewFileName = str_replace('/', DIRECTORY_SEPARATOR, $viewFileName);

        // remove below line to disable view name . seperation
        $viewFileName = str_replace('.', DIRECTORY_SEPARATOR, $viewFileName);

        $filePathArray = $this->isCore ? [Path::frameworkPath(), self::FRAMEWORK_TEMPLATE_DIRECTORY, $viewFileName] : [Path::appPath(), self::TEMPLATE_DIRECTORY, $viewFileName];
        $fullFilePath = implode(DIRECTORY_SEPARATOR, $filePathArray);

        if (!str_ends_with($viewFileName, self::TEMPLATE_EXTENSION))
            $fullFilePath .= self::TEMPLATE_EXTENSION;

        return $fullFilePath;
    }


    public static function clearOutputBuffer()
    {
        while (\ob_get_level())
            \ob_end_clean();
    }

}