Bonjour {!! $user->name !!},

Voici votre code de verification {!! config('app.name') !!} :

CODE: {{ $code }}

Ce code est valable {{ $minutes }} minutes.
Si vous n'etes pas a l'origine de cette demande, ignorez ce message.
