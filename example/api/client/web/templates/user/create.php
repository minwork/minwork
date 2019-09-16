<?php /** @noinspection PhpUndefinedVariableInspection */ ?>
<h3>Create new user</h3>
<form role="form" method="post" action="<?php echo $data['form']['action']; ?>">
    <div class="row">
        <div class="form-group col-xs-3">
          <label for="inputEmail">Email address</label>
          <input type="email" name="User[email]" class="form-control col-xs-4" id="inputEmail" placeholder="Email" value="<?php echo $data['form']['data']['email']; ?>">
        </div>
    </div>
    <div class="row">
      <div class="form-group col-xs-3">
        <label for="inputFirstName">First name</label>
        <input type="text" name="User[first_name]" class="form-control" id="inputFirstName" placeholder="First name" value="<?php echo $data['form']['data']['first_name']; ?>">
      </div>
    </div>
    <div class="row">
      <div class="form-group col-xs-3">
        <label for="inputLastName">Last name</label>
        <input type="text" name="User[last_name]" class="form-control" id="inputLastName" placeholder="Last name" value="<?php echo $data['form']['data']['last_name']; ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>