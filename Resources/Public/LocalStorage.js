(()=>{var e=localStorage,o="plausible_ignore";function t(){return e[o]==="true"}function n(){e[o]="true"}r();function r(){let i=t()?0:5e3;n(),setTimeout(()=>{window.location="/"},i)}})();
