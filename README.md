# ThumbVideo
A Thumbnail generator with an attached cache

This work is based on the `Thumb` thumbnail generator (for pictures) http://github.com/jamiebicknell/Thumb and uses the FFMPEG Thumbnailer https://github.com/Mihailoff/phpffmpegthumbnailer
##Install
Additonally to an php runtime you need the `ffmpegthumbnaler` binary
##Usage
`<img src='thumb.php?src=./videos/movie.mkv&size=400' />`
Seeks to 10% of the movie length and outputs an JPEG.
##Parmeters
## Query Parameters

<table>
    <tr>
        <th>Key</th>
        <th>Example Value</th>
        <th>Default</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>src</td>
        <td>./videos/movie.mkv</td>
        <td></td>
        <td>Absolute path, relative path, or local URL to the source video. Remote URLs are not allowed</td>
    </tr>
    <tr>
        <td>size</td>
        <td>100</td>
        <td>100</td>
        <td>Width of the image</td>
    </tr>
</table>

