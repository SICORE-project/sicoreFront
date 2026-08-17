@extends('layouts.app')

{{--
  PAGE : États de paie des indemnités — route /indemnites/etats-paie.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "indemnites-etats-paie" dans config/module-pages.php.
  Cette page appartient au module Indemnités, pas au moteur Gestion de la paie.
--}}
@section('title', 'SICORE - Generation des etats de paie')
@section('content')
  <x-module-page slug="indemnites-etats-paie" />
@endsection
