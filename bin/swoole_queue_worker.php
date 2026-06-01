<?php

/**
 * Long-running queue worker entrypoint.
 */

define('RADAPTOR_CLI', dirname(__DIR__) . '/');
define('USE_PERSISTENT_CACHE', false);

putenv('RADAPTOR_RUNTIME=swoole');

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

$sessionHandler = new CLISessionHandler();
session_set_save_handler($sessionHandler, true);
session_start();

Kernel::initialize();
RequestContextHolder::setStorage(new SwooleRequestContextStorage());

if (!class_exists(Swoole\Coroutine::class)) {
	fwrite(STDERR, "Swoole extension is not available.\n");

	exit(1);
}

$scope_list = getenv('RADAPTOR_WORKER_SCOPES');
$handlers = RuntimeWorkerHandlerRegistry::getHandlersForScopeList(is_string($scope_list) ? $scope_list : null);
$stop_requested = false;

\Swoole\Coroutine\run(static function () use ($handlers, &$stop_requested): void {
	\Swoole\Process::signal(SIGTERM, static function () use (&$stop_requested): void {
		$stop_requested = true;
	});
	\Swoole\Process::signal(SIGINT, static function () use (&$stop_requested): void {
		$stop_requested = true;
	});

	$should_stop = static function () use (&$stop_requested): bool {
		return $stop_requested;
	};

	RuntimeWorkerLoop::runForever(
		$handlers,
		$should_stop,
		static fn (float $seconds): mixed => \Swoole\Coroutine::sleep($seconds),
		[
			'command' => 'bin/swoole_queue_worker.php',
			'runtime' => 'swoole',
		]
	);
});
