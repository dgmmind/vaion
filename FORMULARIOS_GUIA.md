# 🎯 SISTEMA DE FORMULARIOS UNIVERSALES

## 📋 **CLASES BASE**

### **`.form`** - Clase principal del formulario
- **Propósito**: Define un formulario básico con layout vertical
- **Características**: 
  - Flexbox vertical
  - Gap de 1rem entre elementos
  - Ancho completo
  - Botón submit alineado a la izquierda

```html
<form class="form">
  <div class="form-group">
    <label>Campo:</label>
    <input type="text" name="campo">
  </div>
  <button type="submit" class="btn btn-primary">Enviar</button>
</form>
```

## 🔄 **MODIFICADORES DE LAYOUT**

### **`.form--horizontal`** - Formulario horizontal
- **Propósito**: Organiza campos en fila horizontal
- **Características**:
  - Flexbox horizontal
  - Wrap automático
  - Alineación de botones al final
  - Ideal para filtros y búsquedas

```html
<form class="form form--horizontal">
  <div class="form-group">
    <label>Desde:</label>
    <input type="date" name="start">
  </div>
  <div class="form-group">
    <label>Hasta:</label>
    <input type="date" name="end">
  </div>
  <button type="submit" class="btn btn-primary">Filtrar</button>
</form>
```

### **`.form--filters`** - Formulario de filtros
- **Propósito**: Estilo especial para formularios de filtrado
- **Características**:
  - Fondo gris claro (#f8fafc)
  - Borde y padding
  - Bordes redondeados
  - Ideal para reportes y búsquedas

```html
<form class="form form--horizontal form--filters">
  <!-- Campos de filtro -->
</form>
```

### **`.form--login`** - Formulario de login
- **Propósito**: Estilo especial para formularios de autenticación
- **Características**:
  - Gap mayor entre elementos (1.5rem)
  - Inputs más grandes
  - Estilos de focus especiales
  - Ideal para login/registro

```html
<form class="form form--login">
  <div class="form-group">
    <label>Usuario:</label>
    <input type="text" name="username">
  </div>
  <div class="form-group">
    <label>Contraseña:</label>
    <input type="password" name="password">
  </div>
  <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
</form>
```

## 🧩 **COMPONENTES INTERNOS**

### **`.form-row`** - Fila de campos
- **Propósito**: Agrupa campos relacionados horizontalmente
- **Características**:
  - Flexbox horizontal
  - Gap de 1.5rem entre campos
  - Responsive (se apila en móviles)

```html
<div class="form-row">
  <div class="form-group">
    <label>Nombre:</label>
    <input type="text" name="first_name">
  </div>
  <div class="form-group">
    <label>Apellido:</label>
    <input type="text" name="last_name">
  </div>
</div>
```

### **`.form-group`** - Grupo de campo individual
- **Propósito**: Contiene un label y su input correspondiente
- **Características**:
  - Flex: 1 (crece para llenar espacio)
  - Min-width: 200px
  - Responsive (ancho completo en móviles)

```html
<div class="form-group">
  <label>Email:</label>
  <input type="email" name="email">
</div>
```

## 🎨 **SISTEMA DE BOTONES**

### **Tamaños disponibles:**
- **`.btn`** - Tamaño estándar (2.8rem altura, 1.2rem padding)
- **`.btn-sm`** - Pequeño (2.2rem altura, 0.875rem padding)
- **`.btn-lg`** - Grande (3.4rem altura, 1.6rem padding)
- **`.btn-block`** - Ancho completo

### **Variantes disponibles:**
- **`.btn-primary`** - Azul (acción principal)
- **`.btn-secondary`** - Gris (acción secundaria)
- **`.btn-success`** - Verde (éxito/confirmar)
- **`.btn-danger`** - Rojo (eliminar/cancelar)
- **`.btn-warning`** - Amarillo (advertencia)
- **`.btn-outline`** - Con borde

## 📱 **RESPONSIVE**

### **Comportamiento automático:**
- Los formularios horizontales se apilan en móviles
- Los botones se vuelven de ancho completo en pantallas pequeñas
- Los campos mantienen un ancho mínimo de 200px en desktop

### **Breakpoint:**
```css
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 1rem;
  }
  
  .btn {
    width: 100%;
  }
}
```

## 🔧 **EJEMPLOS DE USO**

### **Formulario de filtros (reports.php):**
```html
<form class="form form--horizontal form--filters">
  <div class="form-row">
    <div class="form-group">
      <label>Desde:</label>
      <input type="date" name="day_start">
    </div>
    <div class="form-group">
      <label>Hasta:</label>
      <input type="date" name="day_end">
    </div>
    <div class="form-group">
      <label>Empleado:</label>
      <select name="employee_id">
        <option value="">Todos</option>
      </select>
    </div>
  </div>
  <button type="submit" class="btn btn-primary">Filtrar</button>
</form>
```

### **Formulario simple (create_day.php):**
```html
<form class="form">
  <div class="form-group">
    <label>Fecha del día:</label>
    <input type="date" name="day_date" required>
  </div>
  <button type="submit" class="btn btn-primary">Crear día</button>
</form>
```

### **Formulario de login (index.php):**
```html
<form class="form form--login">
  <div class="form-group">
    <label>Usuario:</label>
    <input type="text" name="username" required>
  </div>
  <div class="form-group">
    <label>Contraseña:</label>
    <input type="password" name="password" required>
  </div>
  <button type="submit" class="btn btn-primary btn-block">Continuar</button>
</form>
```

## ✅ **BENEFICIOS DEL NUEVO SISTEMA**

1. **Consistencia**: Todos los formularios usan las mismas clases base
2. **Flexibilidad**: Modificadores permiten diferentes layouts
3. **Mantenibilidad**: Cambios centralizados en CSS
4. **Responsive**: Adaptación automática a diferentes pantallas
5. **Reutilización**: Clases que se pueden combinar según necesidades
6. **Semántica**: Nombres de clases claros y descriptivos

## 🚀 **MIGRACIÓN COMPLETADA**

Todos los formularios del proyecto han sido actualizados para usar el nuevo sistema:
- ✅ `manager/reports.php` → `form form--horizontal form--filters`
- ✅ `manager/create_day.php` → `form`
- ✅ `index.php` → `form form--login`
- ✅ `employee/evaluations.php` → `form form--horizontal form--filters`
- ✅ `manager/evaluations.php` → `form form--horizontal`
- ✅ `manager/eva.php` → `form form--horizontal`
