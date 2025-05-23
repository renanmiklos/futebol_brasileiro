<?php
$senha = '1511';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Hash para a senha '$senha': <br><strong>$hash</strong>";
?>






<a href="detalhes-galeria-videos.php?video_id=<?= $video['id'] ?>">
                            <h2><?= htmlspecialchars($video['titulo']) ?></h2>
</a>


<img src="https://img.youtube.com/vi/<?=urlencode(extract_youtube_id($video['url']))?>/mqdefault.jpg" 
                                    alt="<?=htmlspecialchars($video['titulo'])?>" width="95%">
                                <p><?=htmlspecialchars($video['titulo'])?></p>