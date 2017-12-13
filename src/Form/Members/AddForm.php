<?php
/**
 * Copyright (c) necomori LLC (https://necomori.asia)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  Copyright (c) necomori LLC (https://necomori.asia)
 * @since      0.1.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Form\Members;

use App\Form\AppForm;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * リソースの追加処理を行う
 *
 * @package App\Form\Members
 */
class AddForm extends AppForm
{
    /** @var \App\Model\Table\MembersTable */
    public $Members;

    /**
     * Schemaを組み立てて返却
     *
     * @param Schema $schema
     * @return Schema
     */
    protected function _buildSchema(Schema $schema) : Schema
    {
        $schema
            ->addField('email', ['type' => 'string'])
            ->addField('password', ['type' => 'string'])
            ->addField('name', ['type' => 'string'])
            ->addField('nickname', ['type' => 'string'])
            ->addField('hobby1', ['type' => 'integer'])
            ->addField('hobby2', ['type' => 'integer'])
            ->addField('hobby3', ['type' => 'integer']);

        return $schema;
    }

    /**
     * バリデーションルールを組み立てて返却
     *
     * @param Validator $validator
     * @return Validator
     */
    protected function _buildValidator(Validator $validator) : Validator
    {
        $appValidator = new \App\Validation\Validator();

        $appValidator
            ->notEmpty('email', __('この項目は必須です。'))
            ->maxLength('email', 255)
            ->email('email');

        $appValidator
            ->notEmpty('password', __('この項目は必須です。'))
            ->minLength('password', 6)
            ->maxLength('password', 255);

        $appValidator
            ->notEmpty('name', __('この項目は必須です。'))
            ->maxLength('name', 64);

        $appValidator
            ->notEmpty('nickname', __('この項目は必須です。'))
            ->maxLength('nickname', 64);

        $appValidator
            ->notEmpty('hobby1', __('この項目は必須です。'))
            ->integer('hobby1')
            ->add('hobby1', 'isValidHobby', [
                'rule' => [$this, 'isValidHobby'],
                'message' => __('選択された趣味が不正です。'),
            ])
            ->add('hobby1', 'isUniqueHobby', [
                'rule' => function ($value, $context) {
                    if (empty($value)) {
                        return true;
                    }

                    $hobby2 = Hash::get($context, 'data.hobby2');
                    $hobby3 = Hash::get($context, 'data.hobby3');

                    return ((empty($hobby2) || ($value !== $hobby2)) &&
                        (empty($hobby3) || ($value !== $hobby3)));
                },
                'message' => __('趣味2/趣味3と異なったものを選択してください。'),
            ]);

        $appValidator
            ->allowEmpty('hobby2')
            ->integer('hobby2')
            ->add('hobby2', 'isValidHobby', [
                'rule' => [$this, 'isValidHobby'],
                'message' => __('選択された趣味が不正です。'),
            ])
            ->add('hobby2', 'isUniqueHobby', [
                'rule' => function ($value, $context) {
                    if (empty($value)) {
                        return true;
                    }

                    $hobby1 = Hash::get($context, 'data.hobby1');
                    $hobby3 = Hash::get($context, 'data.hobby3');

                    return ((empty($hobby1) || ($value !== $hobby1)) &&
                        (empty($hobby3) || ($value !== $hobby3)));
                },
                'message' => __('趣味1/趣味3と異なったものを選択してください。'),
            ]);

        $appValidator
            ->allowEmpty('hobby3')
            ->integer('hobby3')
            ->add('hobby3', 'isValidHobby', [
                'rule' => [$this, 'isValidHobby'],
                'message' => __('選択された趣味が不正です。'),
            ])
            ->add('hobby3', 'isUniqueHobby', [
                'rule' => function ($value, $context) {
                    if (empty($value)) {
                        return true;
                    }

                    $hobby1 = Hash::get($context, 'data.hobby1');
                    $hobby2 = Hash::get($context, 'data.hobby2');

                    return ((empty($hobby1) || ($value !== $hobby1)) &&
                        (empty($hobby2) || ($value !== $hobby2)));
                },
                'message' => __('趣味1/趣味2となったものを選択してください。'),
            ]);

        return $appValidator;
    }

    /**
     * 準備された選択肢の中に含まれているか？
     *
     * @param mixed $value
     * @return bool
     */
    public function isValidHobby($value) : bool
    {
        if (empty($value)) {
            return true;
        }

        return array_key_exists($value, Configure::read('hobbies'));
    }

    /**
     * ロジックを実行
     *
     * @param array $data
     * @return bool
     */
    protected function _execute(array $data) : bool
    {
        $result = true;

        $this->loadModel('Members');

        try {
            $entity = [
                'email' => Hash::get($data, 'email'),
                'password' => Hash::get($data, 'password'),
                'member_profile' => [
                    'name' => Hash::get($data, 'name'),
                    'nickname' => Hash::get($data, 'nickname'),
                ],
                'member_hobbies' => $this->buildMemberHobbies($data),
            ];

            $member = $this->Members->newEntity($entity, [
                'associated' => [
                    'MemberProfiles',
                    'MemberHobbies',
                ]
            ]);

            $this->Members->save($member);
            $errors = $member->getErrors();
        } catch (\Exception $e) {
            $this->log($e->getMessage(), 'debug');

            $errors = [
                'exception' => $e->getMessage(),
            ];
        }

        if (!empty($errors)) {
            $this->setErrors($errors);
            $result = false;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function buildMemberHobbies(array $data) : array
    {
        $hobbies = [
            [
                'hobby_id' => Hash::get($data, 'hobby1'),
            ],
        ];

        $hobby2 = Hash::get($data, 'hobby2');
        if ($hobby2) {
            $hobbies[] = [
                'hobby_id' => Hash::get($data, 'hobby2'),
            ];
        }

        $hobby3 = Hash::get($data, 'hobby3');
        if ($hobby3) {
            $hobbies[] = [
                'hobby_id' => Hash::get($data, 'hobby3'),
            ];
        }

        return $hobbies;
    }
}
