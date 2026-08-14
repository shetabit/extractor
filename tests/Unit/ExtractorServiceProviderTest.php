<?php

namespace Shetabit\Extractor\Tests\Unit;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\PendingCommand;
use Shetabit\Extractor\Console\Commands\MicroClientMakeCommand;
use Shetabit\Extractor\Console\Commands\MicroClientMiddlewareMakeCommand;
use Shetabit\Extractor\Tests\TestCase;

final class ExtractorServiceProviderTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $generated = [];

    public function testItRegistersTheGeneratorsOfThePackage() : void
    {
        $commands = $this->app->make(Kernel::class)->all();

        $this->assertArrayHasKey('make:extractor-client', $commands);
        $this->assertArrayHasKey('make:extractor-middleware', $commands);
        $this->assertInstanceOf(MicroClientMakeCommand::class, $commands['make:extractor-client']);
        $this->assertInstanceOf(MicroClientMiddlewareMakeCommand::class, $commands['make:extractor-middleware']);
    }

    public function testItGeneratesAMicroClient() : void
    {
        $command = $this->artisan('make:extractor-client', ['name' => 'WeatherClient']);

        $this->assertInstanceOf(PendingCommand::class, $command);

        // `run()` is what executes it: a pending command that is only asserted
        // on runs when it is destroyed, which is after this test is done.
        $this->assertSame(0, $command->run());

        $contents = $this->contentsOf('Http/RemoteRequests/Clients/WeatherClient.php');

        $this->assertStringContainsString('namespace App\Http\RemoteRequests\Clients;', $contents);
        $this->assertStringContainsString('class WeatherClient extends MicroClientAbstract', $contents);
        $this->assertStringContainsString('public function run() : ResponseInterface|null', $contents);
    }

    public function testItGeneratesAMiddleware() : void
    {
        $command = $this->artisan('make:extractor-middleware', ['name' => 'SignRequest']);

        $this->assertInstanceOf(PendingCommand::class, $command);

        // `run()` is what executes it: a pending command that is only asserted
        // on runs when it is destroyed, which is after this test is done.
        $this->assertSame(0, $command->run());

        $contents = $this->contentsOf('Http/RemoteRequests/Middlewares/SignRequest.php');

        $this->assertStringContainsString('namespace App\Http\RemoteRequests\Middlewares;', $contents);
        $this->assertStringContainsString('class SignRequest extends MiddlewareAbstract', $contents);
        $this->assertStringContainsString(
            'public function handle(RequestInterface $request, Closure $next)',
            $contents
        );
    }

    public function testTheGeneratedClassesAreValidPhp() : void
    {
        $command = $this->artisan('make:extractor-client', ['name' => 'ValidClient']);

        $this->assertInstanceOf(PendingCommand::class, $command);

        // `run()` is what executes it: a pending command that is only asserted
        // on runs when it is destroyed, which is after this test is done.
        $this->assertSame(0, $command->run());

        $path = $this->pathOf('Http/RemoteRequests/Clients/ValidClient.php');

        // The stub used to be looked up under `base_path('vendor/shetabit/…')`,
        // which is only ever there when the package sits in that very directory.
        $this->assertFileExists($path);
        $this->assertNotFalse(token_get_all((string) file_get_contents($path), TOKEN_PARSE));
    }

    protected function tearDown() : void
    {
        File::delete($this->generated);

        parent::tearDown();
    }

    private function pathOf(string $path) : string
    {
        $this->generated[] = $full = $this->app->path($path);

        return $full;
    }

    private function contentsOf(string $path) : string
    {
        $path = $this->pathOf($path);

        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }
}
