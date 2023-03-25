<?php

namespace Sicet7\Base\Database;

use Composer\InstalledVersions;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\Configuration as ORMConfiguration;
use Doctrine\Migrations\Configuration\Configuration as MigrationConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Base\ModuleInterface;
use function DI\add;
use function DI\create;
use function DI\get;

final class Module implements ModuleInterface
{
    /**
     * @return array
     * @throws \Exception
     */
    public static function getDefinitions(): array
    {
        return [
            EventManager::class => create(EventManager::class),
            EntityManager::class => create(EntityManager::class)
                ->constructor(
                    get(Connection::class),
                    get(ORMConfiguration::class),
                    get(EventManager::class)
                ),
            EntityManagerInterface::class => get(EntityManager::class),
            ExistingConfiguration::class => create(ExistingConfiguration::class)
                ->constructor(get(MigrationConfiguration::class)),
            ExistingConnection::class => create(ExistingConnection::class)
                ->constructor(get(Connection::class)),
            DependencyFactory::class => function(
                ExistingConfiguration $configuration,
                ExistingConnection $connection,
                ContainerInterface $container
            ): DependencyFactory {
                $factory = DependencyFactory::fromConnection(
                    $configuration,
                    $connection,
                    $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null
                );
                $factory->freeze();
                return $factory;
            },
            Migrator::class => create(Migrator::class)
                ->constructor(
                    'Doctrine Migrations',
                    InstalledVersions::getVersion('doctrine/migrations')
                )->method('addCommands', get('migrator.commands')),

            CurrentCommand::class => create(CurrentCommand::class)
                ->constructor(get(DependencyFactory::class)),
            DumpSchemaCommand::class => create(DumpSchemaCommand::class)
                ->constructor(get(DependencyFactory::class)),
            ExecuteCommand::class => create(ExecuteCommand::class)
                ->constructor(get(DependencyFactory::class)),
            GenerateCommand::class => create(GenerateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            LatestCommand::class => create(LatestCommand::class)
                ->constructor(get(DependencyFactory::class)),
            MigrateCommand::class => create(MigrateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            RollupCommand::class => create(RollupCommand::class)
                ->constructor(get(DependencyFactory::class)),
            StatusCommand::class => create(StatusCommand::class)
                ->constructor(get(DependencyFactory::class)),
            VersionCommand::class => create(VersionCommand::class)
                ->constructor(get(DependencyFactory::class)),
            UpToDateCommand::class => create(UpToDateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            SyncMetadataCommand::class => create(SyncMetadataCommand::class)
                ->constructor(get(DependencyFactory::class)),
            ListCommand::class => create(ListCommand::class)
                ->constructor(get(DependencyFactory::class)),
            DiffCommand::class => create(DiffCommand::class)
                ->constructor(get(DependencyFactory::class)),

            'migrator.commands' => add([
                get(CurrentCommand::class),
                get(DiffCommand::class),
                get(DumpSchemaCommand::class),
                get(ExecuteCommand::class),
                get(GenerateCommand::class),
                get(LatestCommand::class),
                get(ListCommand::class),
                get(MigrateCommand::class),
                get(RollupCommand::class),
                get(StatusCommand::class),
                get(SyncMetadataCommand::class),
                get(UpToDateCommand::class),
                get(VersionCommand::class),
            ]),
        ];
    }
}