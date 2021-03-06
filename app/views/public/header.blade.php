<div id='main-nav' class='gradient' role='navigation'>
	<div class='container'>
		<div id='site-links'>
			<a href='{{{ route('home') }}}' class='logo'>{{{ Config::get('zenith.server_name') }}}</a>
			{{ HTML::link(route('character.online'), 'Who is online?') }}
			{{ HTML::link(route('character.index'), 'Characters') }}
			{{ HTML::link(route('highscores.show'), 'Highscores') }}
			{{ HTML::link(route('house.index'), 'Houses') }}
			<a class='item' href='#TODO'>Guilds</a>
		</div>

		<div id='account-links'>
			@if (Auth::check())
				<a href='{{{ URL::route('account.show') }}}'>Manage account</a>
				<a href='{{{ URL::route('session.destroy') }}}'>Logout</a>
			@else
				<a href='{{{ URL::route('account.create') }}}'>Create account</a>
				<a href='{{{ URL::route('session.create') }}}'>Login</a>
			@endif
		</div>
	</div>
</div>
@section('scripts')
	@parent
	<script defer>$(document).ready(function() { $('#main-nav a[href="{{ Request::url() }}"]').addClass('active'); });</script>
@stop
