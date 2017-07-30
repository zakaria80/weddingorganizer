<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tools extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            exit('Direct access is not allowed. This is a command line tool, use the terminal');
        }
        $this->load->dbforge();
    }

    public function index()
    {
        $this->load->library('migration');
        if ($this->migration->current() === FALSE) {
            show_error($this->migration->error_string());
        }
    }

    public function help()
    {
        $result = "The following are the available command line interface commmands \n\n";
        $result .= "php index.php tools migration \"file_name\"   Create new migration file \n";
        $result .= "php index.php tools migrate \"version_number\" Run all migrations. THe version number is optional. \n";
        $result .= "php index.php tools seeder \"file_name\" Creates a new seed file.\n";
        $result .= "php index.php tools seed \"file_name\" Run the specified seed file.\n";

        echo $result . PHP_EOL;
    }

    public function migration($name)
    {
        $this->make_migration_file($name);
    }

    public function migrate($version = null)
    {
        $this->load->library('migration');

        if ($version != null) {
            if ($this->migration->version($version) === FALSE) {
                show_error($this->migration->error_string());
            } else {
                echo "Migrations run successfully" . PHP_EOL;
            }
            return;
        }

        if ($this->migration->latest() === FALSE) {
            show_error($this->migration->error_string());
        } else {
            echo "Migration run successfully" . PHP_EOL;
        }
    }

    public function migrate_refresh()
    {
      $this->droptable();
      $this->migrate();
    }

    public function droptable()
    {
      $query = $this->db->query('SHOW TABLES');
      $dbname = $this->db->database;
      foreach ($query->result_array() as $tables) {
        $table = $tables['Tables_in_' . $dbname];
        $this->dbforge->drop_table($table);
      }
    }

    protected function make_migration_file($name)
    {
        $date = new DateTime();
        $timestamp = $date->format('YmdHis');

        $table_name = strtolower($name);

        $path = APPPATH . "database/migrations/$timestamp" . "_" . "$name.php";
        $my_migration = fopen($path, 'w') or die('Unable to create migration file!');
        $migration_template = "
          <?php
          defined('BASEPATH') OR exit('No direct script access allowed');
          class Migration_$name extends CI_Migration {
              public function up() {
                  \$this->dbforge->add_field(array(
                      'id' => array(
                          'type' => 'INT',
                          'constraint' => 11,
                          'auto_increment' => TRUE
                      )
                  ));
                  \$this->dbforge->add_key('id',TRUE);
                  \$this->dbforge->create_table('$table_name');
              }

              public function down() {
                  \$this->dbforge->drop_table('$table_name');
              }
          }";

        fwrite($my_migration,$migration_template);
        fclose($my_migration);

        echo "$path migration has successfully been created." . PHP_EOL;
    }

    public function seeder($name) {
      $this->make_seed_file($name);
    }

    public function seed($name) {
      $this->load->library('Seeder');
      $seeder = new Seeder();
      $seeder->call($name);
    }

    protected function make_seed_file($name) {
      $path = APPPATH . "database/seeds/$name.php";

      $my_seed = fopen($path, "w") or die("Unable to create seed file!");

      $seed_template = "<?php
          class $name extends Seeder {

              private \$table = 'users';

              public function run() {
                  \$this->db->truncate(\$this->table);

                  //seed records manually
                  \$data = [
                      'user_name' => 'admin',
                      'password' => '9871'
                  ];
                  \$this->db->insert(\$this->table, \$data);

                  //seed many records using faker
                  \$limit = 33;
                  echo \"seeding \$limit user accounts\";

                  for (\$i = 0; \$i < \$limit; \$i++) {
                      echo \".\";

                      \$data = array(
                          'user_name' => \$this->faker->unique()->userName,
                          'password' => '1234',
                      );

                      \$this->db->insert(\$this->table, \$data);
                  }

                  echo PHP_EOL;
              }
          }
          ";
      fwrite($my_seed, $seed_template);

      fclose($my_seed);

      echo "$path seeder has successfully been created." . PHP_EOL;
    }
}

/* End of file Controllername.php */