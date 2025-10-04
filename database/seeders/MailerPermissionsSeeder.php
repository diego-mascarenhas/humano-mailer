<?php

namespace HumanoMailer\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MailerPermissionsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		// Message permissions
		Permission::firstOrCreate(['name' => 'message.list']);
		Permission::firstOrCreate(['name' => 'message.create']);
		Permission::firstOrCreate(['name' => 'message.show']);
		Permission::firstOrCreate(['name' => 'message.edit']);
		Permission::firstOrCreate(['name' => 'message.store']);
		Permission::firstOrCreate(['name' => 'message.update']);
		Permission::firstOrCreate(['name' => 'message.destroy']);

		// Template permissions
		Permission::firstOrCreate(['name' => 'template.list']);
		Permission::firstOrCreate(['name' => 'template.create']);
		Permission::firstOrCreate(['name' => 'template.show']);
		Permission::firstOrCreate(['name' => 'template.edit']);
		Permission::firstOrCreate(['name' => 'template.store']);
		Permission::firstOrCreate(['name' => 'template.update']);
		Permission::firstOrCreate(['name' => 'template.destroy']);
	}
}

