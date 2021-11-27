<template>
    <h1>Gerenciador de Pacotes</h1>
    <div v-for="module in $modules">
        <li>{{ module.name + ' ' + module.description }}</li>
    </div>
</template>
<script>
export default {
    name: "Index"
}
</script>
