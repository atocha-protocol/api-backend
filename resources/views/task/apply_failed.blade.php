<html>
<body>
<h1>Request error!</h1>
<hr/>
@if ($errors->any())
    <div class="submit-error">
        @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach
    </div>
@endif
</body>
</html>
