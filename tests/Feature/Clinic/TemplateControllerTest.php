<?php

namespace Tests\Feature\Clinic;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Support\Facades\Gate;
use App\Repositories\TemplateRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\TemplateCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;


class TemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $templateRepository;
    protected $templateCategoryRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $clinic = Clinic::factory()->create();

        $user->clinics()->attach($clinic->id);

        $this->actingAs($user);

        $this->templateRepository = Mockery::mock(TemplateRepository::class);
        $this->templateCategoryRepository = Mockery::mock(TemplateCategoryRepository::class);

        $this->app->instance(TemplateRepository::class, $this->templateRepository);
        $this->app->instance(TemplateCategoryRepository::class, $this->templateCategoryRepository);

        Gate::before(fn () => true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function test_template_index_page()
    {
        $this->templateCategoryRepository
            ->shouldReceive('getOverdueTemplates')
            ->once()
            ->andReturn(collect());

        $this->templateRepository
            ->shouldReceive('getPromotionalTemplates')
            ->once()
            ->andReturn(collect());

        $response = $this->get(route('template.index'));

        $response->assertStatus(200);
        $response->assertViewIs('clinic.pages.template.index');
    }

    public function test_template_stored()
    {
        $this->templateRepository
            ->shouldReceive('getPromotionalTemplates')
            ->once()
            ->andReturn([]);

        $template = (object) [
            'template_category_id' => null,
            'campaign_type_id' => 1,
        ];

        $this->templateRepository
            ->shouldReceive('store')
            ->once()
            ->andReturn($template);

        $response = $this->post(route('template.store'), [
            'name' => 'Test Template',
            'message' => 'Hello',
            'campaign_type_id' => 1,
        ]);

        $response->assertRedirect(route('template.index'));
        $response->assertSessionHas('success');
    }
    
    public function test_template_updated()
    {
        $template = (object) [
            'id' => 1,
            'template_category_id' => 2,
            'campaign_type_id' => 1,
        ];

        $this->templateRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($template);

        $this->templateRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($template);

        $response = $this->put(route('template.update'), [
            'id' => 1,
            'name' => 'Updated',
            'message' => 'Updated message',
        ]);

        $response->assertRedirect(route('template.index'));
        $response->assertSessionHas('success');
    }
    
    public function test_template_set_as_default()
    {
        $template = (object) [
            'template_category_id' => 1,
            'campaign_type_id' => 2,
        ];

        $this->templateRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($template);

        $this->templateRepository
            ->shouldReceive('setDefault')
            ->once()
            ->andReturn($template);

        $response = $this->post(route('template.setDefault', [1, 1]));

        $response->assertRedirect(route('template.index'));
        $response->assertSessionHas('success');
    }
    
    public function test_template_deleted()
    {
        $template = (object) [
            'template_category_id' => 1,
            'campaign_type_id' => 2,
        ];

        $this->templateRepository
            ->shouldReceive('find')
            ->once()
            ->andReturn($template);

        $this->templateRepository
            ->shouldReceive('destroy')
            ->once()
            ->andReturn(true);

        $response = $this->delete(route('template.destroy', 1));

        $response->assertRedirect(route('template.index'));
        $response->assertSessionHas('success');
    }

}
