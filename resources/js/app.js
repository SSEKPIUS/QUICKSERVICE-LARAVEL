window.document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.dropdown-item').forEach((e)=>{
        e.addEventListener('click', (e)=>{
            e.target.parentNode.parentNode.parentNode.parentNode
            .querySelector('#section').value = e.target.innerText;
        })
    });
})
