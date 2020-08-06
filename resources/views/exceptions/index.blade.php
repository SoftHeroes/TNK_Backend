<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>EC-GAMING-{{$code}}</title>
    	<link rel="stylesheet" type="text/css"  href="{{ asset('exceptions/style.css') }}">
	
</head>
<body>

    <div id="notfound">
		<div class="notfound">
			<div class="notfound-404">
				<div></div>
				<h1>{{$code}}</h1>
			</div>
			<h2>{{$message}}</h2>
        <a href="{!! route('vDashboard') !!}">Go Back</a>
		</div>
	</div>
    
</body>
</html>