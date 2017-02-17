<?php
/**
 * Created by PhpStorm.
 * User: danneco
 * Date: 11-12-15
 * Time: 13:23
 */
?>
<div class="form-group">
    <select class="field" name="year">
        @for($i = 2017; $i < date('Y')+20; $i++)
            <option value={!! $i !!}>{!! $i !!}</option>
        @endfor
    </select>
</div>

