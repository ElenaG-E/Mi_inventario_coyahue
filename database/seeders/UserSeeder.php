// database/seeders/DatabaseSeeder.php
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crea 100 usuarios de prueba
        Usuario::factory(100)->create(); 
    }
}

