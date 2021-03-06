
    <?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    class Migration_Pemesanan_Rias extends CI_Migration {
        public function up() {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'pemesanan_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 15
                ),
                'rias_id' => array(
                    'type' => 'INT',
                    'constraint' => 11
                )
            ));
            $this->dbforge->add_key('id',TRUE);
            $this->dbforge->create_table('pemesanan_rias');
        }

        public function down() {
            $this->dbforge->drop_table('pemesanan_rias');
        }
    }
