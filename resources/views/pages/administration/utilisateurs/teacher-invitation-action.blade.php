<form method="POST" action="{{ route('utilisateurs.teacher.resend', $id) }}" style="display:inline">
  @csrf
  <button class="table-action" type="submit" title="Renvoyer l’invitation" aria-label="Renvoyer l’invitation"><i class="fa-solid fa-envelope" aria-hidden="true"></i></button>
</form>
