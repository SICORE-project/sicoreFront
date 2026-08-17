@extends('layouts.app')

{{--
  PAGE : Profils et rôles — route /utilisateurs/profils-roles.
  Routes : routes/web.php. Rendu : components/module-page.blade.php.
  Contenu : clé "profils-roles" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion utilisateur.
--}}
@section('title', 'SICORE - Profils / R&ocirc;les')
@section('content')
  <x-module-page slug="profils-roles" />
@endsection
