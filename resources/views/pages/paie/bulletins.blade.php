@extends('layouts.app')

{{--
  PAGE PAIE DYNAMIQUE : Bulletins des salaires — URL /paie/bulletins.
  Consultation en lecture seule : PayrollBulletinController.
  Les critères reprennent les données de l’état des salaires sans le modifier.
--}}
@section('title', 'SICORE - Bulletins des salaires')
@section('content')
  @include('pages.paie.partials.bulletin-filters')
@endsection
