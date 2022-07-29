<html>
<body>
{{--$table->id();--}}
{{--$table->string('task_kind', 50);--}}
{{--$table->string('task_title', 255);--}}
{{--$table->text('task_detail');--}}
{{--$table->bigInteger('task_prize');--}}
{{--$table->timestamps();--}}
@if ($errors->any())
    <div class="submit-error">
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
@endif

<h1>Pay to</h1>
<div>{{$task_id}} / {{$to_addr}}</div>
</body>
</html>
