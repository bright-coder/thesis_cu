<template>
   <div id="app">
       <h1>Hello Test Component</h1>
       <form action="">
           <input type="text" name="" id="" class="form-control" v-model="txt">
       </form>
   </div>
</template>
<script>
export default {
  name: 'test',
  props: ['txt'],
  data() {
      return {
          
      }
  }
}
</script>
