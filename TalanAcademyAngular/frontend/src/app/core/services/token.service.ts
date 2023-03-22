import { Injectable } from '@angular/core';
import { JwtHelperService } from '@auth0/angular-jwt';
import decode from 'jwt-decode';
@Injectable({
  providedIn: 'root'
})
export class TokenService {

  constructor(private jwtHelper: JwtHelperService) { }

  isAuthenticated(): boolean {
    const token  = localStorage.getItem('token') || undefined;
    return !this.jwtHelper.isTokenExpired(token);
  }

  getTokenPyload(){
    const token = localStorage.getItem('token');
    const tokenPayload = token !== null ? decode(token) : {};
    return tokenPayload;
  }
}
