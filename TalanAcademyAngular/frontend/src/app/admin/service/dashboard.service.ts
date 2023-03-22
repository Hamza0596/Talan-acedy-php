import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class DashboardService {
  apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) {}
  getSessions(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/sessions`);
  }
  getCursusStatics(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/cursus/statistics`);
  }

  getUsers(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/users`);
  }
  getAllCursus(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/cursus`);
  }
  getSessionById(id: any): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/session/${id}`);
  }

  getCursus(id: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/cursus/${id}`);
  }

  addStaff(staff: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/admin/users`, staff);
  }

  changeActivation(id: number): Observable<any> {
    return this.http.post<any>(
      `${this.apiUrl}/admin/users/${id}/isactivated`,
      {}
    );
  }

  changeEmail(id: number, email: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/admin/users/${id}/email`, email);
  }

  changeCursusVisibilty(id: number) {
    return this.http.post(`${this.apiUrl}/admin/cursus/${id}/visibility`, {})
  }
  getCususPdf(id:number){
    return this.http.get(`${this.apiUrl}/admin/cursus/${id}/pdf`,{observe:'response',responseType:'blob'})
  }




  getAllCandidatures() : Observable<any> {   
    return this.http.get<any>(`${this.apiUrl}/admin/candidates`)
  }
}
