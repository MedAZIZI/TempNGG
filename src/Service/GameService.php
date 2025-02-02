<?php

namespace App\Service;

class GameService
{
    // Méthode pour générer un numéro secret basé sur la difficulté du jeu
    public function generateSecretNumber(string $difficulty): int
    {
        switch ($difficulty) {
            case 'easy':
                return rand(1, 50);
            case 'medium':
                return rand(1, 100);
            case 'hard':
                return rand(1, 500);
            default:
                return rand(1, 100); 
        }
    }
    // Méthode pour préparer les joueurs avec leurs noms, scores et tentatives
    public function preparePlayers(array $playerNames): array
    {
        $players = [];
        foreach ($playerNames as $name) {
            if (!empty(trim($name))) {
                $players[] = [
                    'name' => trim($name), 
                    'score' => 0,
                    'attempts_used' => 0
                ];
            }
        }
        return $players;
    }
    public function updateGameState(array &$players, int &$currentPlayerIndex, int $secretNumber, int $maxAttempts, int $guess, string &$message): void
{
    $currentPlayer = $players[$currentPlayerIndex];
    $players[$currentPlayerIndex]['attempts_used']++;

    // Vérifier la tentative
    if ($guess == $secretNumber) {
        $message = "Congratulations, {$currentPlayer['name']}! You guessed the right number {$secretNumber}.";
        $players[$currentPlayerIndex]['score']++;
    } elseif ($guess < $secretNumber) {
        $message = "Try a higher number!";
    } else {
        $message = "Try a lower number!";
    }

    // Vérifier si le joueur a utilisé toutes ses tentatives
    if ($players[$currentPlayerIndex]['attempts_used'] >= $maxAttempts) {
        $message .= " {$currentPlayer['name']}, you have used all your attempts.";
        $nextPlayerIndex = ($currentPlayerIndex + 1) % count($players);
        $currentPlayerIndex = $nextPlayerIndex;

        // Vérifier si tous les joueurs ont utilisé toutes leurs tentatives
        $allAttemptsUsed = array_reduce($players, function ($carry, $player) use ($maxAttempts) {
            return $carry && ($player['attempts_used'] >= $maxAttempts);
        }, true);

        if ($allAttemptsUsed) {
            $message = "Unfortunately, no one wins! The right number was {$secretNumber}.";
            $currentPlayerIndex = null; // This indicates that the game is over
        }
    } else {
        $currentPlayerIndex = ($currentPlayerIndex + 1) % count($players);
    }
}
}
