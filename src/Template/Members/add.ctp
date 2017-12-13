<?php
/** @var \App\Form\Members\AddForm $form */
/** @var array $hobbies */
?>
<?= $this->Form->create($form); ?>
<?= $this->Form->control('email', ['required' => false]); ?>
<?= $this->Form->control('password', ['required' => false]); ?>
<?= $this->Form->control('name', ['required' => false]); ?>
<?= $this->Form->control('nickname', ['required' => false]); ?>
<?= $this->Form->control('hobby1', ['required' => false, 'empty' => '--', 'options' => $hobbies]); ?>
<?= $this->Form->control('hobby2', ['required' => false, 'empty' => '--', 'options' => $hobbies]); ?>
<?= $this->Form->control('hobby3', ['required' => false, 'empty' => '--', 'options' => $hobbies]); ?>
<?= $this->Form->submit(); ?>
<?= $this->Form->end(); ?>
