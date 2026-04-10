<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== USERS ====================
        $adminId = DB::table('users')->insertGetId([
            'first_name' => 'Admin',
            'last_name'  => 'Systicket',
            'email'      => 'admin@systicket.fr',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
            'phone'      => '0600000001',
            'status'     => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $collabId1 = DB::table('users')->insertGetId([
            'first_name' => 'Jean',
            'last_name'  => 'Dupont',
            'email'      => 'jean.dupont@systicket.fr',
            'password'   => Hash::make('password'),
            'role'       => 'collaborateur',
            'phone'      => '0600000002',
            'status'     => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $collabId2 = DB::table('users')->insertGetId([
            'first_name' => 'Marie',
            'last_name'  => 'Martin',
            'email'      => 'marie.martin@systicket.fr',
            'password'   => Hash::make('password'),
            'role'       => 'collaborateur',
            'phone'      => '0600000003',
            'status'     => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $clientId1 = DB::table('users')->insertGetId([
            'first_name' => 'Pierre',
            'last_name'  => 'Leroy',
            'email'      => 'pierre.leroy@client.fr',
            'password'   => Hash::make('password'),
            'role'       => 'client',
            'phone'      => '0600000004',
            'status'     => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $clientId2 = DB::table('users')->insertGetId([
            'first_name' => 'Sophie',
            'last_name'  => 'Bernard',
            'email'      => 'sophie.bernard@client.fr',
            'password'   => Hash::make('password'),
            'role'       => 'client',
            'phone'      => '0600000005',
            'status'     => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // ==================== PROJETS ====================
        $projet1Id = DB::table('projets')->insertGetId([
            'name'        => 'Site E-commerce Alpha',
            'description' => 'Développement complet du site e-commerce pour le client Alpha avec paiement en ligne et gestion des stocks.',
            'status'      => 'active',
            'client_id'   => $clientId1,
            'manager_id'  => $collabId1,
            'start_date'  => Carbon::now()->subMonths(2)->toDateString(),
            'end_date'    => Carbon::now()->addMonths(4)->toDateString(),
            'created_at'  => Carbon::now()->subMonths(2),
            'updated_at'  => Carbon::now(),
        ]);

        $projet2Id = DB::table('projets')->insertGetId([
            'name'        => 'Application Mobile Beta',
            'description' => 'Application mobile cross-platform pour la gestion de commandes client Beta.',
            'status'      => 'active',
            'client_id'   => $clientId2,
            'manager_id'  => $collabId2,
            'start_date'  => Carbon::now()->subMonth()->toDateString(),
            'end_date'    => Carbon::now()->addMonths(6)->toDateString(),
            'created_at'  => Carbon::now()->subMonth(),
            'updated_at'  => Carbon::now(),
        ]);

        $projet3Id = DB::table('projets')->insertGetId([
            'name'        => 'Refonte Intranet Gamma',
            'description' => 'Modernisation de l\'intranet existant avec migration vers une stack moderne.',
            'status'      => 'paused',
            'client_id'   => $clientId1,
            'manager_id'  => $collabId1,
            'start_date'  => Carbon::now()->subMonths(6)->toDateString(),
            'end_date'    => Carbon::now()->subMonth()->toDateString(),
            'created_at'  => Carbon::now()->subMonths(6),
            'updated_at'  => Carbon::now(),
        ]);

        // Assign collaborateurs to projects
        DB::table('projet_user')->insert([
            ['projet_id' => $projet1Id, 'user_id' => $collabId1],
            ['projet_id' => $projet1Id, 'user_id' => $collabId2],
            ['projet_id' => $projet2Id, 'user_id' => $collabId2],
            ['projet_id' => $projet3Id, 'user_id' => $collabId1],
        ]);

        // ==================== CONTRATS ====================
        $contrat1Id = DB::table('contrats')->insertGetId([
            'project_id' => $projet1Id,
            'client_id'  => $clientId1,
            'hours'      => 200,
            'rate'       => 85.00,
            'start_date' => Carbon::now()->subMonths(2)->toDateString(),
            'end_date'   => Carbon::now()->addMonths(4)->toDateString(),
            'status'     => 'active',
            'reference'  => 'CTR-2024-001',
            'notes'      => 'Contrat forfaitaire pour le développement du site e-commerce.',
            'created_at' => Carbon::now()->subMonths(2),
            'updated_at' => Carbon::now(),
        ]);

        $contrat2Id = DB::table('contrats')->insertGetId([
            'project_id' => $projet2Id,
            'client_id'  => $clientId2,
            'hours'      => 150,
            'rate'       => 90.00,
            'start_date' => Carbon::now()->subMonth()->toDateString(),
            'end_date'   => Carbon::now()->addMonths(6)->toDateString(),
            'status'     => 'active',
            'reference'  => 'CTR-2024-002',
            'notes'      => 'Contrat TMA pour l\'application mobile.',
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now(),
        ]);

        // ==================== TICKETS ====================
        $ticket1Id = DB::table('tickets')->insertGetId([
            'title'           => 'Page d\'accueil - Slider principal',
            'description'     => 'Intégrer le slider principal de la page d\'accueil avec les visuels fournis par le client.',
            'status'          => 'in-progress',
            'priority'        => 'high',
            'type'            => 'included',
            'project_id'      => $projet1Id,
            'created_by'      => $adminId,
            'estimated_hours' => 8,
            'created_at'      => Carbon::now()->subWeeks(3),
            'updated_at'      => Carbon::now(),
        ]);

        $ticket2Id = DB::table('tickets')->insertGetId([
            'title'           => 'Module de paiement Stripe',
            'description'     => 'Intégration du module de paiement Stripe avec gestion des webhooks.',
            'status'          => 'new',
            'priority'        => 'critical',
            'type'            => 'billable',
            'project_id'      => $projet1Id,
            'created_by'      => $collabId1,
            'estimated_hours' => 16,
            'created_at'      => Carbon::now()->subWeeks(2),
            'updated_at'      => Carbon::now(),
        ]);

        $ticket3Id = DB::table('tickets')->insertGetId([
            'title'           => 'Bug - Panier ne se vide pas',
            'description'     => 'Après validation de commande, le panier ne se vide pas correctement.',
            'status'          => 'done',
            'priority'        => 'high',
            'type'            => 'included',
            'project_id'      => $projet1Id,
            'created_by'      => $clientId1,
            'estimated_hours' => 4,
            'created_at'      => Carbon::now()->subWeeks(4),
            'updated_at'      => Carbon::now()->subWeek(),
        ]);

        $ticket4Id = DB::table('tickets')->insertGetId([
            'title'           => 'Design écrans de connexion',
            'description'     => 'Créer les maquettes et intégrer les écrans de connexion/inscription pour l\'app mobile.',
            'status'          => 'to-validate',
            'priority'        => 'normal',
            'type'            => 'billable',
            'project_id'      => $projet2Id,
            'created_by'      => $collabId2,
            'estimated_hours' => 12,
            'created_at'      => Carbon::now()->subWeeks(2),
            'updated_at'      => Carbon::now(),
        ]);

        $ticket5Id = DB::table('tickets')->insertGetId([
            'title'           => 'API REST - Endpoints catalogue',
            'description'     => 'Développer les endpoints REST pour le catalogue produits de l\'app mobile.',
            'status'          => 'in-progress',
            'priority'        => 'normal',
            'type'            => 'included',
            'project_id'      => $projet2Id,
            'created_by'      => $adminId,
            'estimated_hours' => 20,
            'created_at'      => Carbon::now()->subWeek(),
            'updated_at'      => Carbon::now(),
        ]);

        $ticket6Id = DB::table('tickets')->insertGetId([
            'title'           => 'Optimisation requêtes SQL',
            'description'     => 'Les requêtes de listing des produits sont trop lentes (> 3s). Optimiser les index et les joins.',
            'status'          => 'waiting-client',
            'priority'        => 'high',
            'type'            => 'billable',
            'project_id'      => $projet1Id,
            'created_by'      => $collabId1,
            'estimated_hours' => 6,
            'created_at'      => Carbon::now()->subDays(5),
            'updated_at'      => Carbon::now(),
        ]);

        // Assign collaborateurs to tickets
        DB::table('ticket_user')->insert([
            ['ticket_id' => $ticket1Id, 'user_id' => $collabId1],
            ['ticket_id' => $ticket2Id, 'user_id' => $collabId1],
            ['ticket_id' => $ticket2Id, 'user_id' => $collabId2],
            ['ticket_id' => $ticket3Id, 'user_id' => $collabId2],
            ['ticket_id' => $ticket4Id, 'user_id' => $collabId2],
            ['ticket_id' => $ticket5Id, 'user_id' => $collabId2],
            ['ticket_id' => $ticket6Id, 'user_id' => $collabId1],
        ]);

        // ==================== TEMPS ====================
        DB::table('temps')->insert([
            [
                'user_id'     => $collabId1,
                'ticket_id'   => $ticket1Id,
                'hours'       => 3.5,
                'date'        => Carbon::now()->subDays(3)->toDateString(),
                'description' => 'Intégration du slider avec Swiper.js',
                'created_at'  => Carbon::now()->subDays(3),
            ],
            [
                'user_id'     => $collabId1,
                'ticket_id'   => $ticket1Id,
                'hours'       => 2.0,
                'date'        => Carbon::now()->subDays(2)->toDateString(),
                'description' => 'Responsive du slider pour mobile et tablette',
                'created_at'  => Carbon::now()->subDays(2),
            ],
            [
                'user_id'     => $collabId2,
                'ticket_id'   => $ticket3Id,
                'hours'       => 1.5,
                'date'        => Carbon::now()->subWeeks(2)->toDateString(),
                'description' => 'Debug du vidage panier après commande',
                'created_at'  => Carbon::now()->subWeeks(2),
            ],
            [
                'user_id'     => $collabId2,
                'ticket_id'   => $ticket4Id,
                'hours'       => 6.0,
                'date'        => Carbon::now()->subDays(4)->toDateString(),
                'description' => 'Maquettes Figma + intégration HTML/CSS',
                'created_at'  => Carbon::now()->subDays(4),
            ],
            [
                'user_id'     => $collabId2,
                'ticket_id'   => $ticket4Id,
                'hours'       => 4.0,
                'date'        => Carbon::now()->subDays(3)->toDateString(),
                'description' => 'Tests et ajustements visuels',
                'created_at'  => Carbon::now()->subDays(3),
            ],
            [
                'user_id'     => $collabId2,
                'ticket_id'   => $ticket5Id,
                'hours'       => 5.0,
                'date'        => Carbon::now()->subDays(1)->toDateString(),
                'description' => 'Développement endpoints GET /produits et GET /produits/{id}',
                'created_at'  => Carbon::now()->subDays(1),
            ],
            [
                'user_id'     => $collabId1,
                'ticket_id'   => $ticket6Id,
                'hours'       => 2.5,
                'date'        => Carbon::now()->subDays(1)->toDateString(),
                'description' => 'Analyse des requêtes lentes avec EXPLAIN',
                'created_at'  => Carbon::now()->subDays(1),
            ],
        ]);

        // ==================== COMMENTAIRES ====================
        DB::table('commentaires')->insert([
            [
                'ticket_id'  => $ticket1Id,
                'user_id'    => $collabId1,
                'content'    => 'J\'ai commencé l\'intégration du slider. Les visuels sont bien reçus.',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'ticket_id'  => $ticket1Id,
                'user_id'    => $clientId1,
                'content'    => 'Parfait, merci. Pouvez-vous ajouter une animation de fade entre les slides ?',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'ticket_id'  => $ticket3Id,
                'user_id'    => $collabId2,
                'content'    => 'Bug corrigé. Le problème venait de la session qui n\'était pas réinitialisée.',
                'created_at' => Carbon::now()->subWeeks(2),
            ],
            [
                'ticket_id'  => $ticket4Id,
                'user_id'    => $collabId2,
                'content'    => 'Les écrans de connexion sont terminés. En attente de validation client.',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'ticket_id'  => $ticket6Id,
                'user_id'    => $collabId1,
                'content'    => 'J\'ai identifié les requêtes problématiques. En attente de validation pour ajouter les index.',
                'created_at' => Carbon::now()->subDays(1),
            ],
        ]);

        // ==================== VALIDATIONS ====================
        DB::table('validations')->insert([
            [
                'ticket_id'    => $ticket4Id,
                'user_id'      => $clientId2,
                'status'       => 'validated',
                'comment'      => null,
                'created_at'   => Carbon::now(),
            ],
        ]);
    }
}
