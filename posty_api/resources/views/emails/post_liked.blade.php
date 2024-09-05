<!DOCTYPE html>
<html>
<head>
    <title>Post Liked</title>
</head>
<body>
    <p>Hi {{ $post->user->name }},</p>
    <p>Your post titled {{ $postid }} was liked By {{ $user->name }} </p>
</body>
</html>