YouTube To MP3
============

Esta classe usa o [youtube-mp3.org](https://youtube-mp3.org) para obter um arquivo MP3 de um video do YouTube


Uso:

```php
YoutubeToMP3::get(LINK_DO_YOUTUBE, ACTION);
```

O parametro action define se será redirecionado para o download do arquivo ou se apenas
retornará o link.

**YoutubeToMP3::DOWNLOAD** => Redireciona para o download do arquivo

**YoutubeToMP3::LINK** (default) => Retorna apenas o link

Exemplo de uso:

```php
echo YoutubeToMP3::get('http://www.youtube.com/watch?v=B2m_WnXjqnM', YoutubeToMP3::LINK);
// Outputs: Uma URL para download do MP3
```