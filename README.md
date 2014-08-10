Chernoff Face Generator
===========

The goal of Chernoff faces [1] is to parametrize show a bunch of facial features such as lips, eyes and nose size. 

See also: https://en.wikipedia.org/wiki/Chernoff_face

Author: <b>Alex Khrabrov</b> (alex@mroja.net)

[1] Herman Chernoff, The Use of Faces to Represent Points in K-Dimensional Space Graphically, Journal of the American Statistical Association, vol. 68, no. 342, pp. 361–368, 1973.

Usage instructions
===========

Accepted $_GET parameters:

 * <b>stats</b> - print time and memory statistics on image
 * <b>border</b> - draw border
 * <b>size=N</b> - dimensions of single face in pixels (NxN)
 * <b>sizex=N/sizey=M</b> - X or Y dimension of single face (NxM), used only when size=N is not specified
 * <b>grid=N</b> - generates NxN grid of images
 * <b>code=N1,N2,...,Nn</b> - a list of maximum ten parameters controlling appearance of the face. Each parameter is a float clamped to [1, 10]. If parameter is empty it'll be randomized for each generated image.                       


License
===========

This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
license text below.

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
Version 2, December 2004

Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

0. You just DO WHAT THE FUCK YOU WANT TO.
