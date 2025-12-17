<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Software;
use App\Repositories\InsuranceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminInsurance extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->insuranceRepository = Mockery::mock(InsuranceRepository::class);
        $this->app->instance(InsuranceRepository::class, $this->insuranceRepository);

        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function test_insurance_index_page()
    {
        $response = $this->get(route('admin.insurance.index'));

        $response->assertStatus(200);
    }
    
    public function test_insurance_create_page()
    {
        $response = $this->get(route('admin.insurance.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.insurance.create');
    }

    public function test_insurance_can_be_stored()
    {
        Software::factory()->create([
            'name' => config('constants.GLOBAL.SOFTWARE.DENTAL_4_WINDOWS'),
        ]);

        $this->insuranceRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return isset($data['software_id']) && $data['status'] === 'pending';
            }));

        $response = $this->post(route('admin.insurance.store'), [
            'name' => 'Test Insurance',
        ]);

        $response->assertRedirect(route('admin.insurance.index'));
        $response->assertSessionHas('success');
    }
   
    public function test_edit_insurance_page()
    {
        $insurance = (object) ['id' => 1, 'name' => 'EditTest Insurance'];

        $this->insuranceRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($insurance);

        $response = $this->get(route('admin.insurance.edit', 1));

        $response->assertStatus(200);
        $response->assertViewIs('admin.insurance.edit');
        $response->assertViewHas('insurance');
    }

    public function test_edit_insurance_not_found()
    {
        $this->insuranceRepository
            ->shouldReceive('findOrFail')
            ->once()
            ->andThrow(ModelNotFoundException::class);

        $response = $this->get(route('admin.insurance.edit', 999));

        $response->assertRedirect(route('admin.insurance.index'));
        $response->assertSessionHas('error');
    }
    
    public function test_insurance_updated()
    {
        $this->insuranceRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, Mockery::any())
            ->andReturn(true);

        $response = $this->put(route('admin.insurance.update', 1), [
            'name' => 'UpdatedName Insurance',
        ]);

        $response->assertRedirect(route('admin.insurance.index'));
        $response->assertSessionHas('success');
    }

    public function test_insurance_deleted()
    {
        $this->insuranceRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $response = $this->delete(route('admin.insurance.destroy', 1));

        $response->assertRedirect(route('admin.insurance.index'));
        $response->assertSessionHas('success');
    }

}
