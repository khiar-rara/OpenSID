<?php
class Migrasi_2005_ke_2006 extends CI_model {

	public function up()
	{
		$this->grup_akses_covid19();
		$this->bug_fix_ref_status_covid19(); // untuk yang sudah terlanjur mengkosongkan DB sebelum PR ini disetujui

		// Ubah nama kode status penduduk
		$this->db->where('id', 2)
			->update('tweb_penduduk_status', array('nama' => 'TIDAK TETAP'));

		//Ganti nama folder widget menjadi widgets
		rename('desa/widget', 'desa/widgets');
		rename('desa/upload/widget', 'desa/upload/widgets');
		// Arahkan semua widget statis ubahan desa ke folder desa/widgets
		$list_widgets = $this->db->where('jenis_widget', 2)->get('widget')->result_array();
		foreach ($list_widgets as $widgets)
		{
			$ganti = str_replace('desa/widget', 'desa/widgets', $widgets['isi']); // Untuk versi 20.04-pasca ke atas
			$cek = explode('/', $ganti); // Untuk versi 20.04 ke bawah
			if ($cek[0] !== 'desa' AND $cek[1] === NULL)
			{ // agar migrasi bisa dijalankan berulang kali
				$this->db->where('id', $widgets['id'])->update('widget', array('isi' => 'desa/widgets/'.$widgets['isi']));
			}
		}
		// Sesuaikan dengan sql_mode STRICT_TRANS_TABLES
		$this->db->query("ALTER TABLE outbox MODIFY COLUMN CreatorID text NULL");
		// Hapus field sasaran
		if ($this->db->field_exists('sasaran', 'program_peserta'))
			$this->db->query('ALTER TABLE `program_peserta` DROP COLUMN `sasaran`');
		//tambah kolom email di tabel tweb_penduduk
		if (!$this->db->field_exists('email', 'tweb_penduduk'))
			$this->dbforge->add_column('tweb_penduduk', array(
				'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,
				),
			));
	}

	private function grup_akses_covid19()
	{
		// Menambahkan menu 'Group / Hak Akses' covid19 table 'user_grup'
		$data[] = array(
			'id'=>'5',
			'nama' => 'Satgas Covid-19',
		);

		foreach ($data as $grup)
		{
			$sql = $this->db->insert_string('user_grup', $grup);
			$sql .= " ON DUPLICATE KEY UPDATE
			id = VALUES(id),
			nama = VALUES(nama)";
			$this->db->query($sql);
		}
	}

	private function bug_fix_ref_status_covid19()
	{
		// Tambah Data di Tabel ref_status_covid
		$data = array();
		$data[] = array(
			'id'=>'1',
			'nama' => 'ODP');

		$data[] = array(
			'id'=>'2',
			'nama' => 'PDP');

		$data[] = array(
			'id'=>'3',
			'nama' => 'ODR');

		$data[] = array(
			'id'=>'4',
			'nama' => 'OTG');

		$data[] = array(
			'id'=>'5',
			'nama' => 'POSITIF');

		$data[] = array(
			'id'=>'6',
			'nama' => 'DLL');

		foreach ($data as $status)
		{
			$sql = $this->db->insert_string('ref_status_covid', $status);
			$sql .= " ON DUPLICATE KEY UPDATE
			id = VALUES(id),
			nama = VALUES(nama)";
			$this->db->query($sql);
		}
	}

}
