@extends('layouts.app')

{{--
  PAGE : Permissions — route /utilisateurs/permissions (routes/web.php).
  Rendu partagé : resources/views/components/module-page.blade.php.
  Contenu : clé "permissions" dans config/module-pages.php.
  Menu : config/navigation.php, groupe Gestion utilisateur.
  Cette vue reste courte car le composant partagé construit toute l'interface.
--}}
@section('title', 'SICORE - Permissions')
@section('content')
  <x-module-page slug="permissions" />
@endsection
