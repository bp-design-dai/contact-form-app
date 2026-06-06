<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    public function test_contacts_index_returns_successful_response(): void
    {
        $response = $this->getJson('/api/v1/contacts');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_invalid_gender_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=99');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gender'])
            ->assertJson([
                'message' => '性別の値が不正です',
            ]);
    }

    public function test_invalid_category_returns_validation_error(): void
    {
        $response = $this->getJson('/api/v1/contacts?category_id=999');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id'])
            ->assertJson([
                'message' => '選択されたカテゴリーが存在しません',
            ]);
    }

    public function test_contact_show_returns_contact_detail(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'category',
                    'tags',
                ],
            ]);
    }

    public function test_contact_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }

    public function test_contact_can_be_created(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'yamada@example.com',
        ]);
    }

    public function test_contact_create_returns_validation_error_when_tel_is_invalid(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tel'])
            ->assertJson([
                'message' => '電話番号はハイフンなしの10〜11桁で入力してください',
            ]);
    }

    public function test_contact_create_returns_validation_error_when_tag_does_not_exist(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'tagtest@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [999],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tag_ids.0'])
            ->assertJson([
                'message' => '選択されたタグが存在しません',
            ]);
    }

    public function test_contact_can_be_updated(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'update@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => '更新ビル',
            'category_id' => $category->id,
            'detail' => '更新テストです',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '更新',
        ]);
    }

    public function test_contact_can_be_deleted(): void
    {
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_contact_delete_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }
}
