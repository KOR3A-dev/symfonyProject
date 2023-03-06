
let app = new Vue({
    el: '#app',
    delimiters : ['${', '}'],

    mounted() {
        this.getProducts()
        this.getCategorys()
    },

    data: () => ({
        selected: null,
        showModalCreate: false,
        showCard : false,
        newProduct: {},
        inactiveProducts: [],
        product: {
            productSelect : {},
            products : [],
            productFields : [
                { name: 'title', lable: 'Title'},
                { name: 'description', lable: 'Description'},
                { name: 'code', lable: 'Code'},
                { name: 'price', lable: 'Price'},
                { name: 'stock', lable: 'Stock'},
                { name: 'status', lable: 'Status'},
                { name: 'category', lable: 'Category'},
                { name: 'actions', lable: 'Actions'},
            ],
        },

        category: {
            categorys : []
        }
    }),
    methods: {
    
        /*  Methods Product */
        async getProducts(){
            try {
                const response = await axios.get('http://localhost:8000/products')
                this.product.products = response.data
                .filter(item => item.status === true)
                .map(item => ({ 
                    title: item.title,
                    description :  item.description,
                    code : item.code,
                    price : item.price,
                    stock : item.stock,
                    status : item.status ? "available" : "not available",
                    category : item.category,
                    actions: null
                }))

                this.inactiveProducts = response.data
                .filter(item => item.status !== true)
                .map(item => ({ 
                    title: item.title,
                    description :  item.description,
                    code : item.code,
                    price : item.price,
                    stock : item.stock,
                    status : item.status ? "available" : "not available",
                    category : item.category,
                    actions: null
                }))

            } catch (error) {
              console.error(error)
            }
        },
          
        async getProduct({code}){
            let response =  await axios.get(`http://localhost:8000/product/${code}`)
            this.product.productSelect = response.data            
        },

        async createProduct(){

            /* omit any characters that come with the value price */
            this.newProduct.price = this.newProduct.price.replace(/[\$\.]/g, '').replace(',', '.');
            
            try {
                const formData = new FormData()
                formData.append('title', this.newProduct.title)
                formData.append('description', this.newProduct.description)
                formData.append('code', this.newProduct.code)
                formData.append('price', this.newProduct.price)
                formData.append('stock', this.newProduct.stock)
                formData.append('status', true)
                formData.append('category_id', this.newProduct.category)
                let response = await axios.post('http://localhost:8000/create/product', formData)
                this.showModalCreate = false
                Swal.fire({
                    title: 'Successfully!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonText: 'Ok'
                })
                this.getProducts()
            } catch (error) {
                console.error(error)
            }
        },

        async inactiveProduct({code}){
            let response = await axios.put(`http://localhost:8000/inactivate/product/${code}`)
            this.product.products = this.product.products.filter(p => p.code !== code)
            Swal.fire({
                title: 'Successfully!',
                text: response.data.message,
                icon: 'success',
                confirmButtonText: 'Ok'
            })
            this.getProducts()
        },

        async getCategorys(){
            let request =  await axios.get('http://localhost:8000/categorys')
            this.category.categorys = request.data.map(category => ({
                value: category.id,
                text: category.name
            }))
        },

        onlyNumbers(event) {
            const keyCode = event.keyCode || event.which;
            const isNumber = keyCode >= 48 && keyCode <= 57;
            if (!isNumber) {
                event.preventDefault();
            }
        }, 

        formatPrice(value) {
            value = value.replace(/\D/g, '');
            let parts = [];
      
            while (value.length > 3) {
              parts.unshift(value.slice(-3));
              value = value.slice(0, -3);
            }

            if (value) {
              parts.unshift(value);
            }
      
            this.newProduct.price = '$' + parts.join('.');
        },
    },
})
