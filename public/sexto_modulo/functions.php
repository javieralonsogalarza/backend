<?php
/**
 * Corta un nombre completo para que quepa en un espacio limitado
 *
 * @param string $nombreCompleto El nombre completo de la persona
 * @param string $nombrePila Solo el nombre de pila
 * @param int $maxCaracteres Máximo de caracteres permitidos
 * @param bool $formatoEspecial Si usar formato especial (F. Perez de V.)
 * @return string El nombre formateado
 */
function cortarNombreApellido($nombreCompleto, $nombrePila, $maxCaracteres = 16, $formatoEspecial = true) {
    // 1. Extraer el paréntesis con el número
    preg_match('/\s*\(\d+\)/', $nombreCompleto, $parentesisMatch);
    $parentesis = isset($parentesisMatch[0]) ? $parentesisMatch[0] : '';
    
    // 2. Quitar el paréntesis del nombre completo
    $nombreSinParentesis = trim(str_replace($parentesis, '', $nombreCompleto));
    
    // 3. Verificar si el nombre sin paréntesis ya está dentro del límite
    $limiteAjustado = empty($parentesis) ? $maxCaracteres + 3 : $maxCaracteres;
    if (mb_strlen($nombreSinParentesis, 'UTF-8') <= $limiteAjustado) {
        return $nombreCompleto;
    }
    
    // 4. Si se solicita el formato especial (F. Perez de V.)
    if ($formatoEspecial) {
        // Extraer nombre(s) y apellido(s)
        $nombrePilaPartes = explode(' ', $nombrePila);
        
        // Encontrar dónde termina el nombre y comienzan los apellidos
        $posicionApellidos = mb_strpos($nombreSinParentesis, trim(str_replace($nombrePila, '', $nombreSinParentesis)));
        $apellidos = trim(mb_substr($nombreSinParentesis, $posicionApellidos));
        
        // Dividir los apellidos en partes
        $apellidosPartes = explode(' ', $apellidos);
        
        // CAMBIO: Abreviar todos los nombres, no solo el primero
        $nombresAbreviados = [];
        foreach ($nombrePilaPartes as $parte) {
            $nombresAbreviados[] = mb_substr($parte, 0, 1, 'UTF-8') . '.';
        }
        $nombreAbreviado = implode(' ', $nombresAbreviados);
        
        // Identificar preposiciones
        $preposicionesCortas = ['de', 'la', 'del', 'el', 'da', 'di', 'du', 'y', 'e', 'i'];
        
        // Encontrar el primer apellido y posibles preposiciones
        $primerApellido = $apellidosPartes[0];
        $indiceActual = 1;
        
        // Agregar preposiciones si existen
        while ($indiceActual < count($apellidosPartes) && 
               in_array(mb_strtolower($apellidosPartes[$indiceActual], 'UTF-8'), $preposicionesCortas)) {
            $primerApellido .= ' ' . $apellidosPartes[$indiceActual];
            $indiceActual++;
        }
        
        // Abreviar los apellidos restantes
        $apellidosRestantes = [];
        for ($i = $indiceActual; $i < count($apellidosPartes); $i++) {
            $apellidosRestantes[] = mb_substr($apellidosPartes[$i], 0, 1, 'UTF-8') . '.';
        }
        
        // Construir el nombre formateado
        $nombreFormateado = $nombreAbreviado . ' ' . $primerApellido;
        if (!empty($apellidosRestantes)) {
            $nombreFormateado .= ' ' . implode(' ', $apellidosRestantes);
        }
        
        // Verificar si está dentro del límite
        if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
            return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
        }
        
        // Si excede el límite, recortar el primer apellido
        $espacioRestante = $limiteAjustado - mb_strlen($nombreAbreviado . ' ', 'UTF-8');
        if (!empty($apellidosRestantes)) {
            $espacioRestante -= mb_strlen(' ' . implode(' ', $apellidosRestantes), 'UTF-8');
        }
        
        if ($espacioRestante > 0) {
            $primerApellidoRecortado = mb_substr($primerApellido, 0, $espacioRestante, 'UTF-8');
            $nombreFormateado = $nombreAbreviado . ' ' . $primerApellidoRecortado;
            if (!empty($apellidosRestantes)) {
                $nombreFormateado .= ' ' . implode(' ', $apellidosRestantes);
            }
            return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
        }
    }
    
    // 5. Si no se usa el formato especial o si falló, continuar con el método anterior
    // Dividir el nombre en partes
    $partes = explode(' ', $nombreSinParentesis);
    $totalPartes = count($partes);
    
    // Identificar preposiciones y artículos cortos (2-3 caracteres)
    $preposicionesCortas = ['de', 'la', 'del', 'el', 'da', 'di', 'du', 'y', 'e', 'i'];
    
    // Obtener el apellido final (última parte)
    $apellidoFinal = $partes[$totalPartes - 1];
    
    // Procesar las partes anteriores
    $procesadas = [];
    for ($i = 0; $i < $totalPartes - 1; $i++) {
        // Mantener preposiciones cortas tal cual
        if (in_array(mb_strtolower($partes[$i], 'UTF-8'), $preposicionesCortas) && mb_strlen($partes[$i], 'UTF-8') <= 3) {
            $procesadas[] = $partes[$i];
        } else {
            // Convertir a inicial las que no son preposiciones cortas
            $procesadas[] = mb_substr($partes[$i], 0, 1, 'UTF-8') . '.';
        }
    }
    
    $procesadasStr = implode(' ', $procesadas);
    $nombreFormateado = $procesadasStr . ' ' . $apellidoFinal;
    
    // Verificar si el formato actual está dentro del límite ajustado
    if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
        return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
    }
    
    // Si aún excede, convertir todas las preposiciones a iniciales también
    $iniciales = [];
    for ($i = 0; $i < $totalPartes - 1; $i++) {
        $iniciales[] = mb_substr($partes[$i], 0, 1, 'UTF-8') . '.';
    }
    
    $inicialesStr = implode(' ', $iniciales);
    $nombreFormateado = $inicialesStr . ' ' . $apellidoFinal;
    
    // Verificar de nuevo con el límite ajustado
    if (mb_strlen($nombreFormateado, 'UTF-8') <= $limiteAjustado) {
        return $nombreFormateado . ($parentesis ? ' ' . $parentesis : '');
    }
    
    // Si sigue excediendo, recortar el apellido final
    $espacioDisponible = $limiteAjustado - mb_strlen($inicialesStr . ' ', 'UTF-8');
    if ($espacioDisponible > 0) {
        $apellidoRecortado = mb_substr($apellidoFinal, 0, $espacioDisponible, 'UTF-8');
        return $inicialesStr . ' ' . $apellidoRecortado . ($parentesis ? ' ' . $parentesis : '');
    } else {
        // En caso extremo donde no hay espacio ni para una letra del apellido
        $iniciales = [];
        // Reducir a iniciales sin espacios entre ellas
        for ($i = 0; $i < $totalPartes - 1; $i++) {
            $iniciales[] = mb_substr($partes[$i], 0, 1, 'UTF-8');
        }
        $inicialesSinEspacio = implode('', $iniciales) . '.';
        
        $espacioDisponible = $limiteAjustado - mb_strlen($inicialesSinEspacio . ' ', 'UTF-8');
        $apellidoRecortado = mb_substr($apellidoFinal, 0, $espacioDisponible, 'UTF-8');
        return $inicialesSinEspacio . ' ' . $apellidoRecortado . ($parentesis ? ' ' . $parentesis : '');
    }
}
?>
