import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { TokenService } from '../services/token.service';

@Injectable({
  providedIn: 'root'
})
export class ApprentiGuard implements CanActivate {
  constructor(
    private tokenService :TokenService,
    private router : Router
  ){}
  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
      const user: any = this.tokenService.getTokenPyload();
      if (
        !this.tokenService.isAuthenticated() ||
        !user.roles.includes('ROLE_APPRENTI')
      ) {
        this.router.navigate([''], {queryParams : {returnUrl : state.url}});
        localStorage.clear();
        return false;
      }
      return true;
    }


}
