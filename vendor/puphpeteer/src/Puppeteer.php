<?php
namespace Nesk\Puphpeteer;
require_once $_SERVER['DOCUMENT_ROOT'] . '/euraknos/WebCrawler/vendor/rialto/src/AbstractEntryPoint.php';

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Nesk\Rialto\AbstractEntryPoint;
use vierbergenlars\SemVer\{version, expression, SemVerException};

class Puppeteer extends AbstractEntryPoint
{
    /**
     * Default options.
     *
     * @var array
     */
    protected $options = [
        'read_timeout' => 30,

        // Logs the output of Browser's console methods (console.log, console.debug, etc...) to the PHP logger
        'log_browser_console' => false,
    ];

    /**
     * Instanciate Puppeteer's entry point.
     */
    public function __construct(array $userOptions = [])
    {
        if (!empty($userOptions['logger']) && $userOptions['logger'] instanceof LoggerInterface) {
            $this->checkPuppeteerVersion($userOptions['executable_path'] ?? 'node', $userOptions['logger']);
        }

        parent::__construct(
            __DIR__.'/PuppeteerConnectionDelegate.js',
            new PuppeteerProcessDelegate,
            $this->options,
            $userOptions
        );
    }

    private function checkPuppeteerVersion(string $nodePath, LoggerInterface $logger): void {
        $currentVersion = $this->currentPuppeteerVersion($nodePath);
        $acceptedVersions = $this->acceptedPuppeteerVersion();

        try {
            $semver = new version($currentVersion);
            $expression = new expression($acceptedVersions);

            if (!$semver->satisfies($expression)) {
                $logger->warning(
                    "The installed version of Puppeteer (v$currentVersion) doesn't match the requirements"
                    ." ($acceptedVersions), you may encounter issues."
                );
            }
        } catch (SemVerException $exception) {
            $logger->warning("Puppeteer doesn't seem to be installed.");
        }
    }

    private function currentPuppeteerVersion(string $nodePath): ?string {
        $process = new Process([$nodePath, __DIR__.'/get-puppeteer-version.js']);
        $process->mustRun();

        return json_decode($process->getOutput());
    }

    private function acceptedPuppeteerVersion(): string {
        $npmManifestPath = __DIR__.'/../package.json';
        $npmManifest = json_decode(file_get_contents($npmManifestPath));

        return $npmManifest->dependencies->puppeteer;
    }
}
