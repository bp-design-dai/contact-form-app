<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_page_can_be_displayed(): void
    {
        Category::create(['content' => '商品のお届けについて']);
        Tag::create(['name' => '質問']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    public function test_thanks_page_can_be_displayed(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
        $response->assertViewIs('contact.thanks');
    }

    public function test_confirm_page_can_be_displayed_with_valid_input(): void
    {
        $category = Category::create(['content' => '商品のお届けについて']);
        $tag = Tag::create(['name' => '質問']);

        $response = $this->post('/contacts/confirm', [
            'first_name' => '練習',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都千代田区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('練習');
        $response->assertSee('太郎');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    public function test_contact_can_be_stored(): void
    {
        $category = Category::create(['content' => '商品のお届けについて']);
        $tag = Tag::create(['name' => '質問']);

        $response = $this->post('/contacts', [
            'first_name' => '練習',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都千代田区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect('/thanks');

        $this->assertDatabaseHas('contacts', [
            'first_name' => '練習',
            'last_name' => '太郎',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);
    }
}
