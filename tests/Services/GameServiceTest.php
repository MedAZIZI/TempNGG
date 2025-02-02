<?php

namespace App\Tests\Service;

use App\Service\GameService;
use PHPUnit\Framework\TestCase;

class GameServiceTest extends TestCase
{

    private $gameService;

    protected function setUp(): void
    {
        $this->gameService = new GameService();
    }
    public function testGenerateSecretNumber()
    {
        // Test easy difficulty
        $easyNumber = $this->gameService->generateSecretNumber('easy');
        $this->assertGreaterThanOrEqual(1, $easyNumber);
        $this->assertLessThanOrEqual(50, $easyNumber);

        // Test medium difficulty
        $mediumNumber = $this->gameService->generateSecretNumber('medium');
        $this->assertGreaterThanOrEqual(1, $mediumNumber);
        $this->assertLessThanOrEqual(100, $mediumNumber);

        // Test hard difficulty
        $hardNumber = $this->gameService->generateSecretNumber('hard');
        $this->assertGreaterThanOrEqual(1, $hardNumber);
        $this->assertLessThanOrEqual(500, $hardNumber);

        // Test default difficulty
        $defaultNumber = $this->gameService->generateSecretNumber('unknown');
        $this->assertGreaterThanOrEqual(1, $defaultNumber);
        $this->assertLessThanOrEqual(100, $defaultNumber);
    }

    public function testPreparePlayers()
    {
        // Test with valid player names
        $playerNames = ['nadia', '  bouchra  ', 'cheyma'];
        $players = $this->gameService->preparePlayers($playerNames);

        $this->assertCount(3, $players);
        $this->assertEquals('nadia', $players[0]['name']);
        $this->assertEquals('bouchra', $players[1]['name']);
        $this->assertEquals('cheyma', $players[2]['name']);
        $this->assertEquals(0, $players[0]['score']);
        $this->assertEquals(0, $players[0]['attempts_used']);

        // Test with empty names
        $emptyNames = ['', '  ', 'joe', ''];
        $playersWithEmpty = $this->gameService->preparePlayers($emptyNames);

        $this->assertCount(1, $playersWithEmpty);
        $this->assertEquals('joe', $playersWithEmpty[0]['name']);
    }

    // Test updateGameState method - Correct Guess
    public function testUpdateGameStateCorrectGuess()
    {
        $players = [
            ['name' => 'Player1', 'score' => 0, 'attempts_used' => 0],
            ['name' => 'Player2', 'score' => 0, 'attempts_used' => 0]
        ];
        $currentPlayerIndex = 0;
        $secretNumber = 50;
        $maxAttempts = 5;
        $guess = 50;
        $message = '';

        $this->gameService->updateGameState($players, $currentPlayerIndex, $secretNumber, $maxAttempts, $guess, $message);

        $this->assertEquals(1, $players[0]['score']);
        $this->assertEquals(1, $players[0]['attempts_used']);
        $this->assertEquals(1, $currentPlayerIndex);
        $this->assertStringContainsString('Congratulations', $message);
    }

    // Test updateGameState method - Incorrect Guess (Lower)
    public function testUpdateGameStateIncorrectLowerGuess()
    {
        $players = [
            ['name' => 'Player1', 'score' => 0, 'attempts_used' => 0],
            ['name' => 'Player2', 'score' => 0, 'attempts_used' => 0]
        ];
        $currentPlayerIndex = 0;
        $secretNumber = 50;
        $maxAttempts = 5;
        $guess = 30;
        $message = '';

        $this->gameService->updateGameState($players, $currentPlayerIndex, $secretNumber, $maxAttempts, $guess, $message);

        $this->assertEquals(0, $players[0]['score']);
        $this->assertEquals(1, $players[0]['attempts_used']);
        $this->assertEquals(1, $currentPlayerIndex);
        $this->assertStringContainsString('higher number', $message);
    }

    // Test updateGameState method - Incorrect Guess (Higher)
    public function testUpdateGameStateIncorrectHigherGuess()
    {
        $players = [
            ['name' => 'Player1', 'score' => 0, 'attempts_used' => 0],
            ['name' => 'Player2', 'score' => 0, 'attempts_used' => 0]
        ];
        $currentPlayerIndex = 0;
        $secretNumber = 50;
        $maxAttempts = 5;
        $guess = 70;
        $message = '';

        $this->gameService->updateGameState($players, $currentPlayerIndex, $secretNumber, $maxAttempts, $guess, $message);

        $this->assertEquals(0, $players[0]['score']);
        $this->assertEquals(1, $players[0]['attempts_used']);
        $this->assertEquals(1, $currentPlayerIndex);
        $this->assertStringContainsString('lower number', $message);
    }

    // Test updateGameState method - All Attempts Used
    public function testUpdateGameStateAllAttemptsUsed()
    {
        $players = [
            ['name' => 'Player1', 'score' => 0, 'attempts_used' => 5],
            ['name' => 'Player2', 'score' => 0, 'attempts_used' => 5]
        ];
        $currentPlayerIndex = 0;
        $secretNumber = 50;
        $maxAttempts = 5;
        $guess = 30;
        $message = '';

        $this->gameService->updateGameState($players, $currentPlayerIndex, $secretNumber, $maxAttempts, $guess, $message);

        $this->assertNull($currentPlayerIndex);
        $this->assertStringContainsString('Unfortunately, no one wins', $message);
    }




}