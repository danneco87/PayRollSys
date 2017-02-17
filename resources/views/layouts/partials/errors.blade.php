<?php
/**
 * Created by PhpStorm.
 * User: danneco
 * Date: 11-12-15
 * Time: 14:48
 */
 ?>
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <p>Error</p>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif