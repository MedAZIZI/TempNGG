<?php
namespace App\Form;

use App\Entity\Game;
use App\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numberOfPlayers', IntegerType::class, [
                'attr' => ['min' => 2, 'max' => 10, 'class' => 'form-control'],
                'data' => 2,
                'label' => 'Number of Players',
            ])
            ->add('playerNames', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => false,
                'entry_options' => [
                    'attr' => ['class' => 'form-control'],
                    'label' => false,
                ],
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Easy 1-50' => 'easy',
                    'Medium 1-100' => 'medium',
                    'Hard 1-500' => 'hard',
                ],
                'attr' => ['class' => 'form-select'],
                'label' => 'Difficulty Level',
            ])
            ->add('startGame', SubmitType::class, [
                'label' => 'Start Game',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $game = $event->getData();
                
                // Créer un nouvel objet Player pour chaque nom valide
                if ($game instanceof Game && !empty($game->getPlayerNames())) {
                    foreach ($game->getPlayerNames() as $playerName) {
                        if (!empty(trim($playerName))) {
                            $player = new Player();
                            $player->setName(trim($playerName));
                            $player->setScore(0);
                            $game->addPlayer($player);
                        }
                    }
                }
            });
    }

    // Configuration des options du formulaire (ici, on spécifie la classe de données associée)
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}