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

namespace App\Controller;

use App\Form\Members\AddForm;
use Cake\Core\Configure;

/**
 * Members Controller
 */
class MembersController extends AppController
{
    /**
     * Add method
     *
     * @return void
     */
    public function add() : void
    {
        $form = new AddForm();

        if ($this->request->is('post')) {
            if ($form->execute($this->request->getData())) {
                $this->Flash->success('メンバーの登録に成功しました。');

                $this->render('thanks');
                return;
            }

            $this->Flash->error('入力に問題があります。');
        }

        $hobbies = Configure::read('hobbies');
        $this->set(compact('form', 'hobbies'));
    }
}
