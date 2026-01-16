<?php

namespace App\Console;

use Carbon\Carbon;
use Faker\Factory;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Populate database...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $faker = Factory::create("fr_FR");
        $nbCompanies = 10;

        for ($i = 0; $i < $nbCompanies; $i++) {
            // 2. Création de l'Entreprise
            $companyId = $db->table('companies')->insertGetId([
                'name'       => $faker->company,
                'phone'      => $faker->phoneNumber,
                'email'      => $faker->companyEmail,
                'website'    => $faker->url,
                'image'  => 'https://picsum.photos/640/480?random=' . rand(1, 50000),
                'created_at' => $faker->dateTimeThisYear,
                'updated_at' => Carbon::now(),
            ]);

            $headOfficeId = null;
            $nbOffices = rand(1, 4);

            for ($j = 0; $j < $nbOffices; $j++) {
                $city = $faker->city;

                // 3. Création du Bureau
                $officeId = $db->table('offices')->insertGetId([
                    'name'       => "Bureau de " . $city,
                    'address'    => $faker->streetAddress,
                    'city'       => $city,
                    'zip_code'    => $faker->postcode,
                    'country'    => 'France',
                    'email'      => $faker->email,
                    'company_id' => $companyId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Le premier bureau créé devient le siège social
                if ($j === 0) {
                    $headOfficeId = $officeId;
                }

                $nbEmployees = rand(2, 10);

                // 4. Création des employés (Insertion par lot possible pour la performance, mais boucle ici pour simplifier)
                $employeesData = [];
                for ($k = 0; $k < $nbEmployees; $k++) {
                    $employeesData[] = [
                        'first_name' => $faker->firstName,
                        'last_name'  => $faker->lastName,
                        'office_id'  => $officeId,
                        'email'      => $faker->email,
                        'phone'      => $faker->phoneNumber,
                        'job_title'  => $faker->jobTitle,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                // Insertion en une seule requête pour tous les employés d'un bureau
                $db->table('employees')->insert($employeesData);
            }

            // 5. Mise à jour du siège social
            if ($headOfficeId) {
                $db->table('companies')
                    ->where('id', $companyId)
                    ->update(['head_office_id' => $headOfficeId]);
            }
        }

        $output->writeln('Database created successfully!');
        return 0;
    }
}
