<?php

namespace Tests;

use DI\ContainerBuilder;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Sicet7\Base\Database\Migrator;
use Sicet7\Base\Database\Module as DatabaseModule;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(DatabaseModule::class)]
final class DatabaseModuleTest extends ContainerTestCase
{
    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws \Exception
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
        $builder->addDefinitions(
            DatabaseModule::getDefinitions(),
            [
                Connection::class => function () {
                    return DriverManager::getConnection((new DsnParser())->parse('sqlite3:///:memory:'));
                },
                Configuration::class => function () {
                    return new Configuration();
                },
                \Doctrine\ORM\Configuration::class => function (): \Doctrine\ORM\Configuration {
                    return ORMSetup::createAttributeMetadataConfiguration([
                        __DIR__
                    ], true);
                }
            ]
        );
    }

    #[Test]
    public function canGetAllRegistrations(): void
    {
        $this->checkRegistrations([
            EventManager::class,
            EntityManager::class,
            EntityManagerInterface::class,
            ExistingConfiguration::class,
            ExistingConnection::class,
            DependencyFactory::class,
            Migrator::class,
            CurrentCommand::class,
            DiffCommand::class,
            DumpSchemaCommand::class,
            ExecuteCommand::class,
            GenerateCommand::class,
            LatestCommand::class,
            ListCommand::class,
            MigrateCommand::class,
            RollupCommand::class,
            StatusCommand::class,
            SyncMetadataCommand::class,
            UpToDateCommand::class,
            VersionCommand::class,
        ]);
    }

    #[Test]
    public function canRunMigration(): void
    {
        $input = new StringInput('migrations:migrate --allow-no-migration');
        $input->setInteractive(false);
        $code = self::getContainer()->get(MigrateCommand::class)->run($input, new NullOutput());
        $this->assertEquals(0, $code);
    }
}