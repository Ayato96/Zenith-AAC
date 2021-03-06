@extends('public.master')

@section('content')
	<div class='skill-list'>
		<h3>Categories</h3>
		@foreach($skills as $slug => $values)
			@unless($skill === $slug)
				{{ HTML::link(URL::route('highscores.show', array('skill' => $slug)), $values[1]) }}
			@else
				<a href='#'><strong>{{{ $values[1] }}}</strong></a>
			@endunless
		@endforeach
	</div>
	<div class='highscore-list{{{ $skill === 'level' ? ' level' : '' }}}'>
		<h1>Highscores for {{{ $skills[$skill][1] }}}</h1>
		@if($skill === 'level')
			@include('highscores.level')
		@else
			@include('highscores.generic')
		@endif
		{{ $characters->links() }}
	</div>
@stop
