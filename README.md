/***********************************************************************
 *                    Chernoff face generator                          *
 *               Copyright (c) 2010 Alex Khrabrov                      *
 ***********************************************************************
 * This program is free software. It comes without any warranty, to    *
 * the extent permitted by applicable law. You can redistribute it     *
 * and/or modify it under the terms of the Do What The Fuck You Want   *
 * To Public License, Version 2, as published by Sam Hocevar. See      *
 * license text below.                                                 *
 ***********************************************************************
 *                                                                     *
 *            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE              *
 *                    Version 2, December 2004                         *
 *                                                                     *
 * Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>                    *
 *                                                                     *
 * Everyone is permitted to copy and distribute verbatim or modified   *
 * copies of this license document, and changing it is allowed as long *
 * as the name is changed.                                             *
 *                                                                     *
 *            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE              *
 *   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION   *
 *                                                                     *
 *  0. You just DO WHAT THE FUCK YOU WANT TO.                          *
 *                                                                     *
 ***********************************************************************
 * Accepted $_GET parameters:                                          *
 * -> stats  - print time and memory statistics on image               *
 * -> border - draw border                                             *
 * -> size=N - dimensions of single image (NxN)                        *
 * -> sizex=N/sizey=M - dimensions of single image (NxM), only if      *
 *                      size=N is not specified                        *
 * -> grid=N - generates NxN grid of images                            *
 * -> code=N1,N2,...,Nn - List of maximum ten parameters controlling   *
 *                        appearance of the face. Each parameter is a  *
 *                        float clamped to [1, 10]. If parameter is    *
 *                        empty it'll be taken as a random number for  *
 *                        each generated image.                        *
 ***********************************************************************/
