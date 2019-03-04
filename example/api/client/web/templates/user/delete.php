<?php
use Minwork\Helper\Arr;
?>
<h3>Delete user</h3>
<form role="form" method="post" action="<?php echo $data['form']['action']; ?>">
	<div class="row">
        <div class="form-group col-xs-3">
          <label for="inputUserId">User id</label>
          <input type="number" name="User[id]" class="form-control col-xs-4" id="inputUserId" placeholder="User id" value="<?php echo Arr::getNestedElement($data, 'form.data.id'); ?>">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>