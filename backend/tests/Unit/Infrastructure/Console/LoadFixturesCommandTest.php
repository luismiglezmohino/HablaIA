<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Console;

use App\Infrastructure\Console\LoadFixturesCommand;
use App\Infrastructure\DataFixtures\CategoryFixturesInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

describe('LoadFixturesCommand', function (): void {
    describe('configuration', function (): void {
        it('has command name app:fixtures:load', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $command = new LoadFixturesCommand($categoryFixtures);

            expect($command->getName())->toBe('app:fixtures:load');
        });

        it('has description', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $command = new LoadFixturesCommand($categoryFixtures);

            expect($command->getDescription())->not->toBeEmpty();
        });

        it('has dry-run option', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $command = new LoadFixturesCommand($categoryFixtures);

            $definition = $command->getDefinition();
            expect($definition->hasOption('dry-run'))->toBeTrue();
        });
    });

    describe('execute', function (): void {
        it('loads fixtures and returns success', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $categoryFixtures->expects($this->once())
                ->method('load')
                ->willReturn(7);

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $exitCode = $tester->execute([]);

            expect($exitCode)->toBe(Command::SUCCESS);
            expect($tester->getDisplay())->toContain('7');
            expect($tester->getDisplay())->toContain('categories');
        });

        it('shows message when some categories skipped', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $categoryFixtures->expects($this->once())
                ->method('load')
                ->willReturn(3);

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $exitCode = $tester->execute([]);

            expect($exitCode)->toBe(Command::SUCCESS);
            expect($tester->getDisplay())->toContain('3');
        });

        it('shows message when no categories loaded', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $categoryFixtures->expects($this->once())
                ->method('load')
                ->willReturn(0);

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $exitCode = $tester->execute([]);

            expect($exitCode)->toBe(Command::SUCCESS);
            expect($tester->getDisplay())->toContain('0');
        });
    });

    describe('dry-run mode', function (): void {
        it('does not call load when dry-run is enabled', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);
            $categoryFixtures->expects($this->never())->method('load');

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $exitCode = $tester->execute(['--dry-run' => true]);

            expect($exitCode)->toBe(Command::SUCCESS);
        });

        it('shows categories that would be loaded', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $tester->execute(['--dry-run' => true]);

            $display = $tester->getDisplay();
            expect($display)->toContain('dry-run');
            expect($display)->toContain('Personas');
            expect($display)->toContain('Acciones');
            expect($display)->toContain('Emociones');
            expect($display)->toContain('Lugares');
            expect($display)->toContain('Objetos');
            expect($display)->toContain('Comida');
            expect($display)->toContain('Transporte');
        });

        it('shows icons for each category', function (): void {
            $categoryFixtures = $this->createMock(CategoryFixturesInterface::class);

            $command = new LoadFixturesCommand($categoryFixtures);

            $application = new Application();
            $application->addCommand($command);

            $tester = new CommandTester($command);
            $tester->execute(['--dry-run' => true]);

            $display = $tester->getDisplay();
            expect($display)->toContain('users');
            expect($display)->toContain('play');
            expect($display)->toContain('heart');
            expect($display)->toContain('map-pin');
            expect($display)->toContain('box');
            expect($display)->toContain('utensils');
            expect($display)->toContain('car');
        });
    });
});
