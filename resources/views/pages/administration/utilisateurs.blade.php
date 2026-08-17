@extends('layouts.app')

{{--
  PAGE : Utilisateurs — route /utilisateurs dans routes/web.php.
  Rendu partagé : resources/views/components/module-page.blade.php.
  Contenu : clé "utilisateurs" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion utilisateur.
--}}
@section('title', 'SICORE - Utilisateurs')
@section('content')
  <x-module-page slug="utilisateurs" />
@endsection
