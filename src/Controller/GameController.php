<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Form\GameType;
use App\Form\GuessType;
use App\Service\GameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{

    private $entityManager;
    private $gameService;

    // Le constructeur pour injecter les services nécessaires
    public function __construct(EntityManagerInterface $entityManager, GameService $gameService)
    {
        $this->entityManager = $entityManager;
        $this->gameService = $gameService;
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        // Création du formulaire pour la création du jeu
        $form = $this->createForm(GameType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire (nom des joueurs)
            $game = $form->getData();
            $session = $request->getSession();

            $playerNames = $game->getPlayerNames();
            $playerNames = array_filter($playerNames, function ($name) {
                return !empty(trim($name));
            });

            if (count($playerNames) < 2) {
                $this->addFlash('error', 'Vous devez avoir au moins 2 joueurs');
                return $this->render('game/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Prépare les joueurs en utilisant GameService (ajoute des objets Player à partir des noms)
            $players = $this->gameService->preparePlayers($playerNames);

            // Génère un numéro secret basé sur la difficulté choisie
            $secretNumber = $this->gameService->generateSecretNumber($game->getDifficulty());

            foreach ($players as $playerData) {
                $player = new Player();
                $player->setName($playerData['name']);
                $player->setScore(0); // Initialisation du score à 0
                $player->setGame($game); // Associe le joueur au jeu
                $game->addPlayer($player); // Ajoute le joueur au jeu
            }

            $game->setPlayedAt(new \DateTime());

            // Sauvegarde le jeu et ses joueurs dans la base de données
            $this->entityManager->persist($game);
            $this->entityManager->flush();

            $session->set('gameId', $game->getId());
            $session->set('players', $players);
            $session->set('currentPlayer', 0);
            $session->set('secretNumber', $secretNumber);
            $session->set('maxAttempts', 5);
            $session->set('gameOver', false);
            $session->set('difficulty', $game->getDifficulty());

            // Redirige vers la page de devinette des numéros
            return $this->redirectToRoute('number_guessing');
        }
        return $this->render('game/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/game/number-guessing', name: 'number_guessing')]
    public function numberGuessing(Request $request): Response
    {
        $session = $request->getSession();

        // Validate game session
        if (!$session->has('players') || $session->get('gameOver')) {
            $this->addFlash('error', 'Game session not initialized or game is over.');
            return $this->redirectToRoute('app_index');
        }

        $players = $session->get('players');
        $currentPlayerIndex = $session->get('currentPlayer');
        $secretNumber = $session->get('secretNumber');
        $maxAttempts = $session->get('maxAttempts');

        $form = $this->createForm(GuessType::class);
        $form->handleRequest($request);

        $message = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $guess = $form->get('guess')->getData();

            // Update game state using GameService
            $this->gameService->updateGameState($players, $currentPlayerIndex, $secretNumber, $maxAttempts, $guess, $message);

            // Check if all players have used all attempts and the game is over
            if (strpos($message, 'Unfortunately, no one wins') !== false) {
                $session->set('gameOver', true);
            }

            // Check if player guessed correctly
            if (strpos($message, 'Congratulations') !== false) {
                $session->set('gameOver', true);

                // Save game result for each player
                $game = $this->entityManager->getRepository(Game::class)->find($session->get('gameId'));
                foreach ($players as $playerData) {
                    $player = $this->entityManager->getRepository(Player::class)->findOneBy(['name' => $playerData['name'], 'game' => $game]);
                    if ($player) {
                        $player->setScore($playerData['score']);
                        $this->entityManager->persist($player);
                    }
                }
                $this->entityManager->flush();
            }

            // Update session
            $session->set('players', $players);
            $session->set('currentPlayer', $currentPlayerIndex);
        }

        return $this->render('game/number_guessing.html.twig', [
            'form' => $form->createView(),
            'players' => $players,
            'currentPlayer' => $currentPlayerIndex !== null ? $players[$currentPlayerIndex]['name'] : null,
            'message' => $message,
            'maxAttempts' => $maxAttempts,
            'gameOver' => $session->get('gameOver'),
        ]);
    }

    #[Route('/game/restart', name: 'game_restart')]
    public function restartGame(Request $request): Response
    {
        $session = $request->getSession();

        if (!$session->has('players')) {
            return $this->redirectToRoute('app_index');
        }

        $players = $session->get('players');
        $difficulty = $session->get('difficulty');

        foreach ($players as &$player) {
            $player['attempts_used'] = 0;
        }

        // Generate a new secret number using the same difficulty level
        $secretNumber = $this->gameService->generateSecretNumber($difficulty);

        $session->set('players', $players);
        $session->set('currentPlayer', 0);
        $session->set('secretNumber', $secretNumber);
        $session->set('maxAttempts', 5);
        $session->set('gameOver', false);
        $session->set('difficulty', $difficulty);

        return $this->redirectToRoute('number_guessing');
    }


    #[Route('/game/reset', name: 'game_reset')]
    public function resetGame(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();
        return $this->redirectToRoute('app_index');
    }

    #[Route('/game/list', name: 'game_list')]
    public function list(): Response
    {
        // Fetch all games with their players
        $games = $this->entityManager->getRepository(Game::class)->findAll();

        return $this->render('game/list.html.twig', [
            'games' => $games,
        ]);
    }
}















