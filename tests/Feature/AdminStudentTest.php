<?php

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\School;
use App\Models\SppgUnit;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'username' => 'admin_test',
            'email' => 'admin_test@sim-mbg.test',
            'password_hash' => bcrypt('password123'),
            'full_name' => 'Admin Penguji',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function createSppgAndSchool(): array
    {
        $sppg = SppgUnit::create([
            'code' => 'SPPG-DPS-01',
            'name' => 'SPPG Denpasar Barat 01',
            'region' => 'Kota Denpasar',
            'address' => 'Jl. Gunung Agung No. 45',
            'capacity' => 3000,
            'contact_person' => 'I Wayan Sudarma',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        $school = School::create([
            'npsn' => '50103120',
            'name' => 'SMP Negeri 1 Denpasar',
            'education_level' => 'SMP',
            'district' => 'Denpasar Barat',
            'city' => 'Kota Denpasar',
            'address' => 'Jl. Surapati No. 2',
            'pic_name' => 'I Nyoman Sukra',
            'sppg_id' => $sppg->id,
            'status' => 'active',
        ]);

        return [$sppg, $school];
    }

    public function test_admin_can_view_student_page_with_data_and_kpi(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $student = Student::create([
            'nisn' => '0081234567',
            'name' => 'Made Arya',
            'school_id' => $school->id,
            'class_name' => 'VIII-B',
            'recipient_status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.students'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.students');
        $response->assertViewHas('students');
        $response->assertViewHas('recap');
        $response->assertViewHas('schools');
        $response->assertSee('Made Arya');
        $response->assertSee('0081234567');
        $response->assertSee('SMP Negeri 1 Denpasar');
    }

    public function test_admin_can_filter_students_via_json(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        Student::create([
            'nisn' => '0081111111',
            'name' => 'Ketut Sari',
            'school_id' => $school->id,
            'class_name' => 'VII-A',
            'recipient_status' => 'active',
        ]);

        Student::create([
            'nisn' => '0082222222',
            'name' => 'Wayan Budi',
            'school_id' => $school->id,
            'class_name' => 'IX-C',
            'recipient_status' => 'active',
        ]);

        $response = $this->actingAs($admin)->json('GET', route('admin.students'), [
            'search' => 'Sari',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'students');
        $response->assertJsonPath('students.0.name', 'Ketut Sari');
    }

    public function test_admin_can_create_student_with_allergies(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $response = $this->actingAs($admin)->json('POST', route('admin.students.store'), [
            'nisn' => '0098765432',
            'name' => 'Ni Komang Ayu',
            'school_id' => $school->id,
            'class_name' => 'X IPA 1',
            'menu_type' => 'Menu Khusus',
            'allergies' => 'Kacang, Seafood',
            'recipient_status' => 'Aktif',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('students', [
            'nisn' => '0098765432',
            'name' => 'Ni Komang Ayu',
            'school_id' => $school->id,
            'class_name' => 'X IPA 1',
            'recipient_status' => 'active',
        ]);

        $student = Student::where('nisn', '0098765432')->first();
        $this->assertNotNull($student);
        $this->assertEquals(2, $student->allergies()->count());

        $this->assertDatabaseHas('activity_logs', [
            'title' => 'Siswa Baru Ditambahkan',
        ]);
    }

    public function test_admin_can_create_student_via_standard_form_post_with_redirect(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'nisn' => '0098765499',
            'name' => 'Wayan Standard Form',
            'school_id' => $school->id,
            'class_name' => 'VIII-D',
            'menu_type' => 'Reguler',
            'recipient_status' => 'Aktif',
        ]);

        $response->assertRedirect(route('admin.students'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nisn' => '0098765499',
            'name' => 'Wayan Standard Form',
        ]);
    }

    public function test_create_student_validates_required_fields_and_unique_nisn(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        Student::create([
            'nisn' => '0099999999',
            'name' => 'Siswa Eksis',
            'school_id' => $school->id,
            'class_name' => 'VII-A',
            'recipient_status' => 'active',
        ]);

        $response = $this->actingAs($admin)->json('POST', route('admin.students.store'), [
            'nisn' => '0099999999', // duplicate
            'name' => '',
            'school_id' => 9999, // non-existent
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nisn', 'name', 'school_id']);
    }

    public function test_admin_can_view_single_student_detail_json(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $student = Student::create([
            'nisn' => '0083333333',
            'name' => 'Gede Putra',
            'school_id' => $school->id,
            'class_name' => 'XII IPS 2',
            'recipient_status' => 'active',
        ]);

        $allergen = Allergen::create(['name' => 'Telur']);
        $student->allergies()->attach($allergen->id);

        $response = $this->actingAs($admin)->json('GET', route('admin.students.show', $student->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Gede Putra');
        $response->assertJsonPath('data.allergies', 'Telur');
        $response->assertJsonPath('data.menu_type', 'Menu Khusus');
    }

    public function test_admin_can_update_student_data(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $student = Student::create([
            'nisn' => '0084444444',
            'name' => 'Luh Putu',
            'school_id' => $school->id,
            'class_name' => 'IX-B',
            'recipient_status' => 'active',
        ]);

        $response = $this->actingAs($admin)->json('PUT', route('admin.students.update', $student->id), [
            'nisn' => '0084444444',
            'name' => 'Luh Putu Cantika',
            'school_id' => $school->id,
            'class_name' => 'IX-A',
            'recipient_status' => 'Nonaktif',
            'menu_type' => 'Reguler',
            'allergies' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Luh Putu Cantika',
            'class_name' => 'IX-A',
            'recipient_status' => 'inactive',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'title' => 'Data Siswa Diperbarui',
        ]);
    }

    public function test_admin_can_delete_student(): void
    {
        $admin = $this->createAdminUser();
        [$sppg, $school] = $this->createSppgAndSchool();

        $student = Student::create([
            'nisn' => '0085555555',
            'name' => 'Kadek Sujana',
            'school_id' => $school->id,
            'class_name' => 'XI MIPA 3',
            'recipient_status' => 'active',
        ]);

        $response = $this->actingAs($admin)->json('DELETE', route('admin.students.destroy', $student->id));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'title' => 'Data Siswa Dihapus',
        ]);
    }
}
