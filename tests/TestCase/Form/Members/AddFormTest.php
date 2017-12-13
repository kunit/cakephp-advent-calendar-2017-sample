<?php
namespace App\Test\Form\Members;

use App\Form\Members\AddForm;
use App\Model\Table\MemberHobbiesTable;
use App\Model\Table\MemberProfilesTable;
use App\Model\Table\MembersTable;
use Cake\Datasource\ModelAwareTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

class AddFormTest extends TestCase
{
    public $fixtures = [
        'app.members',
        'app.member_profiles',
        'app.member_hobbies',
    ];

    use ModelAwareTrait;

    /** @var AddForm */
    protected $form;

    /** @var MembersTable */
    protected $Members;

    /** @var MemberProfilesTable */
    protected $MemberProfiles;

    /** @var MemberHobbiesTable */
    protected $MemberHobbies;

    public function setUp() : void
    {
        parent::setUp();

        $this->form = new AddForm();
    }

    public function tearDown() : void
    {
        $this->form = null;

        parent::tearDown();
    }

    public function test_001_必須項目のバリデーションが正しく行われる()
    {
        $data = [
            'email' => null,
            'password' => null,
            'name' => null,
            'nickname' => null,
            'hobby1' => null,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);

        $keys = [
            'email',
            'password',
            'name',
            'nickname',
            'hobby1',
        ];

        foreach ($keys as $key) {
            $this->assertNotNull(Hash::get($this->form->errors(), "{$key}._empty"), sprintf('%s のバリデーション結果が期待通りではない', $key));
        }
    }

    public function test_002_文字数チェックのバリデーションが正しく行われる()
    {
        $data = [
            'email' => str_repeat('a', 256),
            'password' => str_repeat('a', 256),
            'name' => str_repeat('a', 65),
            'nickname' => str_repeat('a', 65),
            'hobby1' => null,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);

        $keys = [
            'email',
            'password',
            'name',
            'nickname',
        ];

        foreach ($keys as $key) {
            $this->assertNotNull(Hash::get($this->form->errors(), "{$key}.maxLength"), sprintf('%s のバリデーション結果が期待通りではない', $key));
        }
    }

    public function test_003_メールアドレスのバリデーションが正しく行われる()
    {
        $data = [
            'email' => 'aaa@bbb',
            'password' => null,
            'name' => null,
            'nickname' => null,
            'hobby1' => null,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);
        $this->assertNotNull(Hash::get($this->form->errors(), 'email.email'));
    }

    public function test_004_パスワードの最低長のバリデーションが正しく行われる()
    {
        $data = [
            'email' => null,
            'password' => '12345',
            'name' => null,
            'nickname' => null,
            'hobby1' => null,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);
        $this->assertNotNull(Hash::get($this->form->errors(), 'password.minLength'));
    }

    public function test_005_趣味の範囲外チェックのバリデーションが正しく行われる()
    {
        $data = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 6,
            'hobby2' => 7,
            'hobby3' => 8,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);
        $this->assertNotNull(Hash::get($this->form->errors(), 'hobby1.isValidHobby'));
        $this->assertNotNull(Hash::get($this->form->errors(), 'hobby2.isValidHobby'));
        $this->assertNotNull(Hash::get($this->form->errors(), 'hobby3.isValidHobby'));
    }

    public function test_006_趣味の重複チェックのバリデーションが正しく行われる()
    {
        $data = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 1,
            'hobby2' => 1,
            'hobby3' => 2,
        ];

        $actual = $this->form->execute($data);

        $this->assertFalse($actual);
        $this->assertNotNull(Hash::get($this->form->errors(), 'hobby1.isUniqueHobby'));
        $this->assertNotNull(Hash::get($this->form->errors(), 'hobby2.isUniqueHobby'));
        $this->assertNull(Hash::get($this->form->errors(), 'hobby3.isUniqueHobby'));
    }

    public function test_007_すべての項目に問題がなければ、正しく保存される()
    {
        $data = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 1,
            'hobby2' => 2,
            'hobby3' => 3,
        ];

        $actual = $this->form->execute($data);

        $this->assertTrue($actual);
        $this->assertEmpty($this->form->errors());

        $this->loadModel('Members');
        $this->loadModel('MemberProfiles');
        $this->loadModel('MemberHobbies');

        $this->assertSame(1, $this->Members->find()->count());
        $this->assertSame(1, $this->MemberProfiles->find()->count());
        $this->assertSame(3, $this->MemberHobbies->find()->count());

        $members = $this->Members->find()->toArray();
        $this->assertSame('test@example.jp', Hash::get($members, '0.email'));
        $this->assertSame('123456', Hash::get($members, '0.password'));

        $memberProfiles = $this->MemberProfiles->find()->toArray();
        $this->assertSame('テスト太郎', Hash::get($memberProfiles, '0.name'));
        $this->assertSame('taro', Hash::get($memberProfiles, '0.nickname'));

        $memberHobbies = $this->MemberHobbies->find()->toArray();
        $this->assertSame(1, Hash::get($memberHobbies, '0.hobby_id'));
        $this->assertSame(2, Hash::get($memberHobbies, '1.hobby_id'));
        $this->assertSame(3, Hash::get($memberHobbies, '2.hobby_id'));
    }

    public function test_008_趣味が2項目しかない場合でも、正しく保存される()
    {
        $data = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 1,
            'hobby2' => null,
            'hobby3' => 3,
        ];

        $actual = $this->form->execute($data);

        $this->assertTrue($actual);
        $this->assertEmpty($this->form->errors());

        $this->loadModel('Members');
        $this->loadModel('MemberProfiles');
        $this->loadModel('MemberHobbies');

        $this->assertSame(1, $this->Members->find()->count());
        $this->assertSame(1, $this->MemberProfiles->find()->count());
        $this->assertSame(2, $this->MemberHobbies->find()->count());

        $members = $this->Members->find()->toArray();
        $this->assertSame('test@example.jp', Hash::get($members, '0.email'));
        $this->assertSame('123456', Hash::get($members, '0.password'));

        $memberProfiles = $this->MemberProfiles->find()->toArray();
        $this->assertSame('テスト太郎', Hash::get($memberProfiles, '0.name'));
        $this->assertSame('taro', Hash::get($memberProfiles, '0.nickname'));

        $memberHobbies = $this->MemberHobbies->find()->toArray();
        $this->assertSame(1, Hash::get($memberHobbies, '0.hobby_id'));
        $this->assertSame(3, Hash::get($memberHobbies, '1.hobby_id'));
    }

    public function test_009_趣味が1項目しかない場合でも、正しく保存される()
    {
        $data = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 1,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data);

        $this->assertTrue($actual);
        $this->assertEmpty($this->form->errors());

        $this->loadModel('Members');
        $this->loadModel('MemberProfiles');
        $this->loadModel('MemberHobbies');

        $this->assertSame(1, $this->Members->find()->count());
        $this->assertSame(1, $this->MemberProfiles->find()->count());
        $this->assertSame(1, $this->MemberHobbies->find()->count());

        $members = $this->Members->find()->toArray();
        $this->assertSame('test@example.jp', Hash::get($members, '0.email'));
        $this->assertSame('123456', Hash::get($members, '0.password'));

        $memberProfiles = $this->MemberProfiles->find()->toArray();
        $this->assertSame('テスト太郎', Hash::get($memberProfiles, '0.name'));
        $this->assertSame('taro', Hash::get($memberProfiles, '0.nickname'));

        $memberHobbies = $this->MemberHobbies->find()->toArray();
        $this->assertSame(1, Hash::get($memberHobbies, '0.hobby_id'));
    }

    public function test_010_既に登録されているメールアドレスの場合は、保存されない()
    {
        $data1 = [
            'email' => 'test@example.jp',
            'password' => '123456',
            'name' => 'テスト太郎',
            'nickname' => 'taro',
            'hobby1' => 1,
            'hobby2' => null,
            'hobby3' => null,
        ];

        $actual = $this->form->execute($data1);

        $this->assertTrue($actual);
        $this->assertEmpty($this->form->errors());

        $data2 = [
            'email' => 'test@example.jp',
            'password' => '789012',
            'name' => 'テスト次郎',
            'nickname' => 'jiro',
            'hobby1' => 1,
            'hobby2' => 2,
            'hobby3' => 3,
        ];

        $actual = $this->form->execute($data2);

        $this->assertFalse($actual);
        $this->assertNotNull(Hash::get($this->form->errors(), 'email._isUnique'));
    }
}
