<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Bref\SymfonyBridge\BrefKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BrefKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }

    public function boot()
    {
        $this->logToStderr(scandir($this->getCacheDir()));
        $this->logToStderr('Boot !');
        $this->logToStderr($this->getCacheDir());
        $this->logToStderr($this->isLambda() ? 'true' : 'false');
        $this->logToStderr(!is_dir($this->getCacheDir()) ? 'true' : 'false');
        $this->logToStderr(($this->isLambda() && ! is_dir($this->getCacheDir())) ? 'true' : 'false');
        parent::boot();
    }

    public function getWritableCacheDirectories(): array
    {
        return ['pools'];
    }

//    public function getBuildDir(): string
//    {
//        return $this->getProjectDir().'/var/build/'.$this->environment;
//    }

    protected function prepareCacheDir(string $readOnlyDir, string $writeDir): void
    {
        $this->logToStderr('Before if');
        if (! is_dir($readOnlyDir)) {
            return;
        }
        $this->logToStderr('After if');
        $startTime = microtime(true);
        $cacheDirectoriesToCopy = $this->getWritableCacheDirectories();
        $filesystem = new Filesystem;
        $filesystem->mkdir($writeDir);

        $scandir = scandir($readOnlyDir, SCANDIR_SORT_NONE);
        if ($scandir === false) {
            return;
        }

        foreach ($scandir as $item) {
            if (in_array($item, ['.', '..'])) {
                continue;
            }

            // Copy directories to a writable space on Lambda.
            if (in_array($item, $cacheDirectoriesToCopy)) {
                $filesystem->mirror("$readOnlyDir/$item", "$writeDir/$item");
                continue;
            }

            // Symlink all other directories
            // This is especially important with the Container* directories since it uses require_once statements
            if (is_dir("$readOnlyDir/$item")) {
                $filesystem->symlink("$readOnlyDir/$item", "$writeDir/$item");
                continue;
            }

            // Copy all other files.
            $filesystem->copy("$readOnlyDir/$item", "$writeDir/$item");
        }

        $this->logToStderr(sprintf(
            'Symfony cache directory prepared in %s ms.',
            number_format((microtime(true) - $startTime) * 1000, 2)
        ));
    }

    protected function logToStderr(string $message): void
    {
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

        $log->warning($message);
    }
}
