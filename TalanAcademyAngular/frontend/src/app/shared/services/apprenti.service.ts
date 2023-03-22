import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, ReplaySubject, Subject } from 'rxjs';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class ApprentiService {
  apiUrl = environment.apiUrl;
  synopsis$ = new Subject();
  backResponse$ = new ReplaySubject();
  constructor(private http: HttpClient) {}

  getSession(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/apprentice/profile`);
  }

  getCorrection(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/apprentice/corrections`);
  }

  getAllCorrection(studentId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/apprentice/corrections-all/${studentId}`);
  }

  
  saveCorrection(formData: any): Observable<any> {
    return this.http.post<any>(
      `${this.apiUrl}/apprentice/corrections`,
      formData
    );
  }

  getCurrentLesson(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/apprentice/session-content`);
  }

  getDayInformation(studentId: number): Observable<any> {
    return this.http.get<any>(
      `${this.apiUrl}/apprentice/dashboard/${studentId}`
    );
  }

  getCursusImage(id: number) {
    return this.http.get(`${this.apiUrl}/apprentice/curriculum/image/${id}`, {
      responseType: 'blob',
    });
  }

  saveReview(dayId: number, avis: any): Observable<any> {
    return this.http.post<any>(
      `${this.apiUrl}/apprentice/review/${dayId}`,
      avis
    );
  }

  getStudentReview(dayId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/apprentice/review/${dayId}`);
  }

  // set synopsis(value: any) {
  //   this._synopsis = value;
  // }
  //
  // get synopsis(): any {
  //   return this._synopsis;
  // }
}
