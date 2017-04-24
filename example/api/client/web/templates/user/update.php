<?php
use Minwork\Helper\ArrayHelper;
?>
<h3>Update user</h3>
<form role="form" method="post" action="<?php echo $data['form']['action']; ?>">
	<div class="row">
        <div class="form-group col-xs-3">
          <label for="inputUserId">User id</label>
          <input type="number" name="User[id]" class="form-control col-xs-4" id="inputUserId" placeholder="User id" value="<?php echo ArrayHelper::getNestedElement($data, 'form.data.id'); ?>">
        </div>
    </div>
	<div class="row">
        <div class="form-group col-xs-3">
          <label for="inputOldEmail">Old email</label>
          <input type="email" name="User[email]" class="form-control col-xs-4" id="inputOldEmail" placeholder="Old email" value="<?php echo ArrayHelper::getNestedElement($data, 'form.data.email'); ?>">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-xs-3">
          <label for="inputEmail">New email</label>
          <input type="email" name="User[new_email]" class="form-control col-xs-4" id="inputEmail" placeholder="New email" value="<?php echo ArrayHelper::getNestedElement($data, 'form.data.new_email'); ?>">
        </div>
    </div>
    <div class="row">
      <div class="form-group col-xs-3">
        <label for="inputFirstName">First name</label>
        <input type="text" name="User[first_name]" class="form-control" id="inputFirstName" placeholder="First name" value="<?php echo ArrayHelper::getNestedElement($data, 'form.data.first_name'); ?>">
      </div>
    </div>
    <div class="row">
      <div class="form-group col-xs-3">
        <label for="inputLastName">Last name</label>
        <input type="text" name="User[last_name]" class="form-control" id="inputLastName" placeholder="Last name" value="<?php echo ArrayHelper::getNestedElement($data, 'form.data.last_name'); ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>