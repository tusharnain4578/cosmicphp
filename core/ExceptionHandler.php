<?php

namespace Core;

use Core\Response;
use Error;
use \Exception;
use Throwable;


class ExceptionHandler
{
    private Response $response;
    private string $environment;
    private static bool $ddStyleApplied = false;

    public function __construct()
    {
        $this->response = response();
        $this->environment = env('ENVIRONMENT', 'production');
    }
    private function isProduction(): bool
    {
        return $this->environment === 'production';
    }
    private function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }
    public function handle(Exception|Error $e)
    {
        if (request()->isCli()) {
            throw $e;
        }

        $viewName = null;
        $viewData = [];

        if ($this->isProduction()) {
            $viewName = 'errors.error_exception';
        } else {
            $viewName = 'errors.internal.error_exception';
            $viewData = [
                'title' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'exception' => $e
            ];
        }

        View::clearOutputBuffer();

        $view = (new View)->core()->render($viewName, data: $viewData);

        $this->response->setStatusCode(statusCode: 500)->send($view);
    }

    public static function dump(...$data)
    {
        // Include CSS for styling
        if (!request()->isCli())
            echo self::ddStyleString();

        foreach ($data as $dt) {
            if (request()->isCli())
                var_dump($dt);
            else {
                echo '<div class="__debug-output"><pre>';
                echo var_dump(escapeHtml($dt));
                echo '</pre></div>';
            }
        }
    }
    public static function dumpAndDie(...$data)
    {
        View::clearOutputBuffer();

        // Output buffering
        ob_start();

        // Include CSS for styling
        if (!request()->isCli())
            echo self::ddStyleString();

        // Dump the variables
        foreach ($data as $dt) {
            if (request()->isCli()) {
                var_dump($dt);
            } else {
                echo '<div class="__debug-output"><pre>';
                echo var_dump(escapeHtml($dt));
                echo '</pre></div>';
            }
        }

        // Get the output and clean the buffer
        $output = ob_get_clean();

        // Send the response with status code 500 and output
        response()->setStatusCode(500)->send($output);
        die;
    }


    public static function showFile(string $file, int $lineNumber, int $lines = 15): bool|string
    {
        if ($file === '' || !is_readable($file)) {
            return false;
        }

        if (function_exists('ini_set')) {
            ini_set('highlight.comment', '#767a7e; font-style: italic');
            ini_set('highlight.default', '#c7c7c7');
            ini_set('highlight.html', '#06B');
            ini_set('highlight.keyword', '#f1ce61;');
            ini_set('highlight.string', '#869d6a');
        }

        try {
            $source = file_get_contents($file);
        } catch (Throwable $e) {
            return false;
        }

        $source = str_replace(["\r\n", "\r"], "\n", $source);
        $source = explode("\n", highlight_string($source, true));

        if (PHP_VERSION_ID < 80300) {
            $source = str_replace('<br />', "\n", $source[1]);
            $source = explode("\n", str_replace("\r\n", "\n", $source));
        } else {
            $source = str_replace(['<pre><code>', '</code></pre>'], '', $source);
        }

        $start = max($lineNumber - (int) round($lines / 2), 0);

        $source = array_splice($source, $start, $lines, true);

        $format = '% ' . strlen((string) ($start + $lines)) . 'd';

        $out = '';
        $spans = 0;

        foreach ($source as $n => $row) {
            $spans += substr_count($row, '<span') - substr_count($row, '</span');
            $row = str_replace(["\r", "\n"], ['', ''], $row);

            if (($n + $start + 1) === $lineNumber) {
                preg_match_all('#<[^>]+>#', $row, $tags);

                $out .= sprintf(
                    "<span class='line highlight'><span class='number'>{$format}</span> %s\n</span>%s",
                    $n + $start + 1,
                    strip_tags($row),
                    implode('', $tags[0])
                );
            } else {
                $out .= sprintf('<span class="line"><span class="number">' . $format . '</span> %s', $n + $start + 1, $row) . "\n";
                $spans++;
            }
        }

        if ($spans > 0) {
            $out .= str_repeat('</span>', $spans);
        }

        return '<pre><code>' . $out . '</code></pre>';
    }


    private static function ddStyleString(): string
    {
        if (self::$ddStyleApplied)
            return '';
        self::$ddStyleApplied = true;
        return '<style>
                .__debug-output {
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    padding: 10px;
                    margin: 10px;
                    font-family: monospace;
                    white-space: pre-wrap;
                    overflow: none;
                }
                .__debug-output pre {
                    margin: 0;
                    background-color: #f1f1f1;
                    padding: 10px;
                    border-radius: 5px;
                }
            </style>';
    }
}