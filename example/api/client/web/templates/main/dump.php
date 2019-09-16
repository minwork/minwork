<?php
/** @noinspection PhpUndefinedVariableInspection */
/** @var JsonResponse $response */

use Example\ApiClient\App\Main\Utility\JsonResponse;
use Minwork\Error\Basic\FieldError;
use Minwork\Http\Interfaces\RequestInterface;

$response = $data['response'];
/** @var RequestInterface $request */
$request = $data['request'];
?>
<h3 style="color: <?php echo $response->isSuccess() ? 'green' : 'red'; ?>;">
	<?php echo $response->isSuccess() ? 'Success' : 'Failure'; ?>
</h3>
<p>
<b>Address</b>: <?php echo $request->getUrl(); ?><br>
<b>Method</b>: <?php echo $request->getMethod(); ?><br>
<?php if (!empty($request->getBody())): ?>
    <h3>Body</h3>
    <pre><?php print_r($request->getBody()); ?></pre>
<?php endif; ?>
<?php if ($response->hasErrors()): ?>
    <h3>Errors</h3>
    <?php if (($errors = $response->getErrorsStorage()->getErrors(\Minwork\Error\Object\Error::TYPE))): ?>
        <b>Global</b><br>
        <ul>
        <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
   	<?php if (($errors = $response->getErrorsStorage()->getErrors(FieldError::TYPE))): ?>
        <b>Form</b><br>
        <ul>
        <?php foreach ($errors as $field => $error): ?>
            <li><?php echo "[{$field}] {$error}"; ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
<?php if (! empty($response->getData())): ?>
    <h3>Result</h3>
    <pre><?php print_r($response->getData()); ?></pre>
<?php endif; ?>
<hr>
<a href="<?php echo $data['route']; ?>">
    <button type="button" class="btn btn-primary">
    	<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>&nbsp;Go Back
    </button>
</a>