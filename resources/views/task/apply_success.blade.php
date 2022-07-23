<html>
<body>
<h1>Create a task</h1>
<hr/>
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

<form action="{{ route('task.apply') }}" accept-charset="UTF-8" method="post">
    @csrf
    <div>
        task_kind: <input name="task_kind" type="text" value="Default" />
    </div>
    <div>
        task_title: <input name="task_title" type="text" />
    </div>
    <div>
        task_detail: <textarea name="task_detail"></textarea>
    </div>
    <div>
        task_prize: <input name="task_prize" type="text" />
    </div>
    <div>
        <button type="submit">Submit</button>
    </div>
</form>
</body>
</html>
